var AnalyticsExternalModule = {
	youTubeSelector: 'iframe[src*="youtube.com"]',
	vimeoSelector: 'iframe[src*="vimeo.com"]',
	elementsToInitializeLater: [],
	elementsInitialized: [],
	init: function(){
		AnalyticsExternalModule.trackVideos()
		AnalyticsExternalModule.trackFieldChanges()
	},
	trackVideos: function(){
		var selector = AnalyticsExternalModule.youTubeSelector + ', ' + AnalyticsExternalModule.vimeoSelector

		// Handle videos configured to display inline
		$(selector).each(function(index, element){
			AnalyticsExternalModule.handleVideoElement(element)
		})

		// Handle videos configured to display inside a popup
		new MutationObserver(function(mutations) {
			mutations.forEach(function(mutation) {
				var nodes = mutation.addedNodes
				if (!nodes) {
					return
				}

				for(var i=0; i<nodes.length; i++){
					var element = nodes[i]
					if(!element){
						return
					}

					if($(element).is(selector)){
						AnalyticsExternalModule.handleVideoElement(element)
					}
				}
			})
		}).observe(document.body, {childList: true, subtree: true})
	},
	trackFieldChanges: function(){
		var log = function(name){
			ExternalModules.Vanderbilt.AnalyticsExternalModule.log('field changed', {
				name: name
			})
		}

		var form = $('#form')

		form.find('input, textarea, select').change(function(){
			var name = this.name

			if(this.type == 'radio'){
				name = name.replace('___radio', '')
			}
			else if(this.type == 'checkbox'){
				name = name.replace('__chkn__', '')
			}

			log(name)
		})

		var handleSlide = function(element){
			element = $(element)
			if(element.hasClass('ui-slider-handle')){
				element = element.parent()
			}

			var name = element[0].id.replace('slider-', '')
			log(name)
		}

		form.find('.ui-slider-horizontal, .ui-slider-handle').on('click', function () {
			handleSlide(this)
		})

		form.find('.ui-slider-horizontal').on('touchend', function () {
			handleSlide(this)
		})
	},
	handleVideoElement: function(element){
		if(this.elementsInitialized.indexOf(element) !== -1){
			// We've already initialized this element.
			return
		}

		element = $(element)

		if(element.is(this.youTubeSelector)){
			element = this.handleYouTubeElement(element)
		}
		else if(element.is(this.vimeoSelector)){
			element = this.handleVimeoElement(element)
		}
		else{
			simpleDialog("The Analytics module couldn't track one of the videos on this page because it is not hosted on YouTube or Vimeo.")
		}

		if(element) {
			this.elementsInitialized.push(element)
		}
	},
	handleYouTubeElement: function(element){
		if(typeof YT == 'undefined' || !YT.loaded){
			// The YouTube framework hasn't loaded yet. Delay initialization.
			this.elementsToInitializeLater.push(element[0])

			// Hide the element to prevent the user from playing it until we are able to track it.
			element.css('visibility', 'hidden')

			return null
		}

		var height = element.attr('height')
		var width = element.attr('width')
		var src = element.attr('src').split('/').pop().split('?')[0]

		var fieldName = AnalyticsExternalModule.getFieldNameForElement(element)
		var newElement = $('<div></div>')
		element.replaceWith(newElement)

		var module = this
		var player = new YT.Player(newElement[0], {
			height: height,
			width: width,
			videoId: src,
			events: {
				'onStateChange': function(e){
					var code = e.data

					var event = null
					if(code == YT.PlayerState.PLAYING){
						event = 'play'
					}
					else if(code == YT.PlayerState.PAUSED){
						event = 'pause'
					}
					else if(code == YT.PlayerState.ENDED){
						event = 'ended'
					}

					if(event){
						module.logVideoEvent(fieldName, event)
					}
				}
			}
		})

		return player.a
	},
	handleVimeoElement: function(element){
		element = element[0]

		var module = this
		var player = new Vimeo.Player(element)
		var fieldName = AnalyticsExternalModule.getFieldNameForElement(element)

		;['play', 'pause', 'ended'].forEach(function(event){
			player.on(event, function() {
				module.logVideoEvent(fieldName, event)
			})
		})

		return element
	},
	getFieldNameForElement: function(element){
		var rowId = $(element).closest('tr').attr('id')
		var parts = rowId.split('-')

		var name = parts[0]

		if(parts[1] !== 'tr'){
			name = 'An error occurred while detecting the field name for row id: ' + rowId
			alert(name)
		}

		return name
	},
	logVideoEvent: function(fieldName, event){
		// Normalize to past tense
		if(event === 'play'){
			event += 'ed'
		}
		else if (event === 'pause') {
			event += 'd'
		}

		ExternalModules.Vanderbilt.AnalyticsExternalModule.log('video ' + event, {
			name: fieldName
		})
	}
}

$(function(){
	AnalyticsExternalModule.init();
})

// This is called by the YouTube Iframe framework
function onYouTubeIframeAPIReady(){
	AnalyticsExternalModule.elementsToInitializeLater.forEach(function(element){
		AnalyticsExternalModule.handleVideoElement(element)
	})
}