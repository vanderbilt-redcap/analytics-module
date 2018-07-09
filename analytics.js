var AnalyticsExternalModule = {
	elementsToInitializeLater: [],
	handleVideoElement: function(element){
		element = $(element)

		if(typeof YT == 'undefined' || !YT.loaded){
			// The YouTube framework hasn't loaded yet. Delay initialization.
			AnalyticsExternalModule.elementsToInitializeLater.push(element[0])

			// Hide the element to prevent the user from playing it until we are able to track it.
			element.css('visibility', 'hidden')

			return
		}

		var height = element.attr('height')
		var width = element.attr('width')
		var src = element.attr('src').split('/').pop().split('?')[0]

		var newElement = $('<div></div>')
		element.replaceWith(newElement)

		new YT.Player(newElement[0], {
			height: height,
			width: width,
			videoId: src,
			events: {
				'onStateChange': function(e){
					var code = e.data

					// TODO: need to handle more states to correctly log (buffered, ended, etc.)
					if(code == YT.PlayerState.PLAYING){
						console.log('video playing')
					}
					else if(code == YT.PlayerState.PAUSED){
						console.log('video paused')
					}
				}
			}
		})
	}
}

$(function(){
	var selector = 'embed[src*="youtube.com"]'

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