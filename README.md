# Analytics

This module logs common user actions.  It also provides a report for analyzing those logs, as well as logs from other modules.  It is still very basic, but could easily be expanded.  The reporting functionality especially will require more work before being particularly useful to the average user.  [Pull requests](https://help.github.com/articles/about-pull-requests/) are always welcome!

This module **HAS NOT BEEN TESTED** with longitudinal projects or repeating instruments. 
 
### Basic Events
The following basic events are logged along with the specified parameters:

- **survey page loaded**
	- **page** - The page number that was loaded
- **field changed**
	- **field** - The name of the field that was changed
- **survey complete**

### Video Events
The following events are logged for videos attached to **Descriptive Text** fields:

- **video played**
- **video paused**
- **video seeked**
- **video ended**
	
For each video event, the following parameters are stored:
- **field** - The name of the **Descriptive Text** field to which the video is attached
- **seconds** - The play position of the video in seconds
