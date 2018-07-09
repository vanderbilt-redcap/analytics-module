<?php
namespace Vanderbilt\AnalyticsExternalModule;

class AnalyticsExternalModule extends \ExternalModules\AbstractExternalModule{
	function redcap_survey_page(){
		$this->initializeJavascriptModuleObject();
		?>
		<script src="https://www.youtube.com/iframe_api"></script>
		<script src="https://player.vimeo.com/api/player.js"></script>
		<script src="<?=$this->getUrl('analytics.js')?>"></script>
		<?php
	}
}
