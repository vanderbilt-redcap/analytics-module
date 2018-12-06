<?php
namespace Vanderbilt\AnalyticsExternalModule;

class AnalyticsExternalModule extends \ExternalModules\AbstractExternalModule{
	static $COLUMNS = [
		'log_id' => "Log ID",
		'timestamp' => 'Timestamp',
		'username' => 'Username',
		'ip' => 'IP Address',
		'external_module_id' => 'External Module',
		'record' => 'Record',
		'message' => 'Message'
	];

	function redcap_survey_page($project_id, $record, $instrument){
		$this->log('survey page loaded', [
			'page' => $_GET['__page__'],
			'instrument' => $instrument
		]);

		$this->initializeJavascriptModuleObject();
		?>
		<script src="https://www.youtube.com/iframe_api"></script>
		<script src="https://player.vimeo.com/api/player.js"></script>
		<script src="<?=$this->getUrl('analytics.js')?>"></script>
		<?php

		if($_SERVER['HTTP_HOST'] === 'localhost' && PHP_MAJOR_VERSION !== 5 || PHP_MINOR_VERSION !== 4){
			?>
			<script>
				alert("Please test the <?=$this->getModuleName()?> module in PHP 5.4 for STRIDE, since UMass (and maybe UAB) are currently on 5.4.")
			</script>
			<?php
		}
	}

	function redcap_survey_complete($project_id, $record, $instrument){
		$this->log('survey complete', [
			'instrument' => $instrument
		]);
	}
}
