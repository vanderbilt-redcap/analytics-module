<?php
namespace Vanderbilt\AnalyticsExternalModule;

class AnalyticsExternalModule extends \ExternalModules\AbstractExternalModule{
	function redcap_survey_page(){
		?>
		<script src="https://www.youtube.com/iframe_api"></script>
		<script src="<?=$this->getUrl('analytics.js')?>"></script>
		<?php
	}
}
