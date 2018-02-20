<?php
namespace Vanderbilt\AnalyticsExternalModule;

class AnalyticsExternalModule extends \ExternalModules\AbstractExternalModule{
	function redcap_survey_page(){
		$this->getSharedContent();
	}

	function redcap_data_entry_form(){
		$this->getSharedContent();
	}

	private function getSharedContent(){
		?>
		<script src="https://www.youtube.com/iframe_api"></script>
		<script src="<?=$this->getUrl('analytics.js')?>"></script>
		<?php
	}
}
