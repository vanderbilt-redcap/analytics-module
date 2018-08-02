<?php
namespace Vanderbilt\AnalyticsExternalModule;

class AnalyticsExternalModule extends \ExternalModules\AbstractExternalModule{
	function redcap_survey_page(){
		$this->log('survey page loaded', [
			'page' => $_GET['__page__']
		]);

		$this->initializeJavascriptModuleObject();
		?>
		<script src="https://www.youtube.com/iframe_api"></script>
		<script src="https://player.vimeo.com/api/player.js"></script>
		<script src="<?=$this->getUrl('analytics.js')?>"></script>
		<?php
	}
}
