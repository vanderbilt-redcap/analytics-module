var AnalyticsExternalModule = {
	youTubeSelector: 'iframe[src*="youtube.com"]',
	vimeoSelector: 'iframe[src*="vimeo.com"]',
	elementsInitialized: {},
	init: function(){
		AnalyticsExternalModule.trackVideos()
		AnalyticsExternalModule.trackFieldChanges()
	},
	trackVideos: function(){
		// YT may always be loaded by this point these days. We'll leave the following block in place just in case.
		if(typeof YT == 'undefined' || !YT.loaded){
			// The YouTube framework hasn't loaded yet. Delay initialization.

			setTimeout(function(){
				AnalyticsExternalModule.trackVideos()
			}, 50)

			return
		}

		var selector = AnalyticsExternalModule.youTubeSelector + ', ' + AnalyticsExternalModule.vimeoSelector

		// Handle videos configured to display inline
		$(selector).each(function(index, element){
			AnalyticsExternalModule.handleVideoElement(element)
		})

		// Handle videos configured to display inside a popup
		new MutationObserver(function(mutations) {
			mutations.forEach(function(mutation) {
				if(mutation.removedNodes.length > 0){
					for(var fieldName in AnalyticsExternalModule.elementsInitialized){
						var element = AnalyticsExternalModule.elementsInitialized[fieldName]
						if(!element.parentElement || !element.parentElement.parentElement || !element.parentElement.parentElement.parentElement){
							// This video must have been inside a popup that was since closed.
							// Log the close event so we can determine when the user stopped watching the video.
							AnalyticsExternalModule.logVideoEvent(fieldName, 'popup closed')
							delete AnalyticsExternalModule.elementsInitialized[fieldName]
						}
					}
				}

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
				field: name
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
		for(var fieldName in this.elementsInitialized){
			if(element === this.elementsInitialized[fieldName]){
				// We've already initialized this element.
				return
			}
		}

		element = $(element)

		var result
		if(element.is(this.youTubeSelector)){
			result = this.handleYouTubeElement(element)
		}
		else if(element.is(this.vimeoSelector)){
			result = this.handleVimeoElement(element)
		}
		else{
			simpleDialog("The Analytics module couldn't track one of the videos on this page because it is not hosted on YouTube or Vimeo.")
		}

		if(result) {
			this.elementsInitialized[result.fieldName] = result.element
		}
		else{
			simpleDialog("An error occurred while tracking video events.  Please report this error to an administrator.")
		}
	},
	handleYouTubeElement: function(element){
		var height = element.attr('height')
		var width = element.attr('width')
		var src = element.attr('src').split('/').pop().split('?')[0]
		var parent = element.parent()

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
						module.logVideoEvent(fieldName, event, e.target.getCurrentTime())
					}
				}
			}
		})

		return {
			fieldName: fieldName,
			element: parent.find('iframe')[0] // Get the new iframe created by the YouTube library
		}
	},
	handleVimeoElement: function(element){
		element = element[0]

		var module = this
		var player = new Vimeo.Player(element)
		var fieldName = AnalyticsExternalModule.getFieldNameForElement(element)

		;['play', 'pause', 'ended', 'seeked'].forEach(function(event){
			player.on(event, function() {
				player.getCurrentTime().then(function(seconds) {
					module.logVideoEvent(fieldName, event, seconds)
				})
			})
		})

		return {
			fieldName: fieldName,
			element: element
		}
	},
	getFieldNameForElement: function(element){
		element = $(element)

		var id = element.attr('id')
		var popupIdPrefix = 'rc-embed-video'

		var name = null
		if(id && id.indexOf(popupIdPrefix) === 0){
			name = id.substr(popupIdPrefix.length+1)
		}
		else{
			var rowId = element.closest('tr').attr('id')
			var parts = rowId.split('-')

			if(parts[1] === 'tr') {
				name = parts[0]
			}
		}
		
		if(!name){
			alert('An error occurred while detecting a field name for logging!  Please report this error.')
		}

		return name
	},
	logVideoEvent: function(fieldName, event, seconds){
		if(seconds){
			seconds = seconds.toFixed(2)
		}

		// Normalize to past tense
		if(event === 'play'){
			event += 'ed'

			if(window.OddcastAvatarExternalModule){
				// If the avatar module is enabled, make sure it is not speaking currently.
				OddcastAvatarExternalModule.stopSpeech()
			}
		}
		else if (event === 'pause') {
			event += 'd'
		}

		ExternalModules.Vanderbilt.AnalyticsExternalModule.log('video ' + event, {
			field: fieldName,
			seconds: seconds
		})
	}
}

$(function(){
	AnalyticsExternalModule.init();
})
