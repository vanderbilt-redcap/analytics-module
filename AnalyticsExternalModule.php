<?php
namespace Vanderbilt\AnalyticsExternalModule;

class AnalyticsExternalModule extends \ExternalModules\AbstractExternalModule{
	const COLUMNS = [
		'log_id' => "Log ID",
		'timestamp' => 'Timestamp',
		'username' => 'Username',
		'ip' => 'IP Address',
		'external_module_id' => 'External Module',
		'project_id' => 'Project',
		'record' => 'Record',
		'message' => 'Message'
	];

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

	function redcap_survey_complete(){
		$this->log('survey complete');
	}
}
