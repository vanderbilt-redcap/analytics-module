var AnalyticsExternalModule = {
	youTubeSelector: 'iframe[src*="youtube.com"]',
	vimeoSelector: 'iframe[src*="vimeo.com"]',
	elementsToInitializeLater: [],
	elementsInitialized: [],
	handleVideoElement: function(element){
		if(AnalyticsExternalModule.elementsInitialized.indexOf(element) !== -1){
			// We've already initialized this element.
			return
		}

		element = $(element)

		if(element.is(AnalyticsExternalModule.youTubeSelector)){
			element = AnalyticsExternalModule.handleYouTubeElement(element)
		}
		else if(element.is(AnalyticsExternalModule.vimeoSelector)){
			element = AnalyticsExternalModule.handleVimeoElement(element)
		}
		else{
			simpleDialog("The Analytics module couldn't track one of the videos on this page because it is not hosted on YouTube or Vimeo.")
		}

		if(element) {
			AnalyticsExternalModule.elementsInitialized.push(element)
		}
	},
	handleYouTubeElement: function(element){
		if(typeof YT == 'undefined' || !YT.loaded){
			// The YouTube framework hasn't loaded yet. Delay initialization.
			AnalyticsExternalModule.elementsToInitializeLater.push(element[0])

			// Hide the element to prevent the user from playing it until we are able to track it.
			element.css('visibility', 'hidden')

			return null
		}

		var height = element.attr('height')
		var width = element.attr('width')
		var src = element.attr('src').split('/').pop().split('?')[0]

		var newElement = $('<div></div>')
		element.replaceWith(newElement)

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
						AnalyticsExternalModule.logVideoEvent(element[0], event)
					}
				}
			}
		})

		return player.a
	},
	handleVimeoElement: function(element){
		element = element[0]

		var player = new Vimeo.Player(element)

		;['play', 'pause', 'ended'].forEach(function(event){
			player.on(event, function() {
				AnalyticsExternalModule.logVideoEvent(element, event)
			})
		})

		return element
	},
	logVideoEvent: function(element, event){
		// Normalize to past tense
		if(event === 'play'){
			event += 'ed'
		}
		else if (event === 'pause') {
			event += 'd'
		}

		console.log('video ' + event, {
			url: element.src
		})
	}
}

$(function(){
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
})

// This is called by the YouTube Iframe framework
function onYouTubeIframeAPIReady(){
	AnalyticsExternalModule.elementsToInitializeLater.forEach(function(element){
		AnalyticsExternalModule.handleVideoElement(element)
	})
}