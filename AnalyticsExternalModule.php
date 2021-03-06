<?php
namespace Vanderbilt\AnalyticsExternalModule;

class AnalyticsExternalModule extends \ExternalModules\AbstractExternalModule{
	const SECONDS_PER_MINUTE = 60;
	const SECONDS_PER_HOUR = self::SECONDS_PER_MINUTE*60;
	const SECONDS_PER_DAY = self::SECONDS_PER_HOUR*24;

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
		if($this->isLoggingDisabled()){
			return;
		}

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

		if($_SERVER['HTTP_HOST'] === 'localhost' && (PHP_MAJOR_VERSION !== 5 || PHP_MINOR_VERSION !== 4)){
			?>
			<script>
				//alert("Please test the <?=$this->getModuleName()?> module in PHP 5.4 for STRIDE, since UMass (and maybe UAB) are currently on 5.4.")
			</script>
			<?php
		}
	}

	function redcap_survey_complete($project_id, $record, $instrument){
		if($this->isLoggingDisabled()){
			return;
		}

		$this->log('survey complete', [
			'instrument' => $instrument
		]);
	}

	private function isLoggingDisabled(){
		return $this->getProjectSetting('disable-logging');
	}

	function getReportWhereClause(){
		$startDate = \db_real_escape_string($_GET['start-date']);
		$endDate = \db_real_escape_string($_GET['end-date']);

		// Bump the end date to the next day so all events on the day specified are include
		$endDate = $this->formatDate(strtotime($endDate) + self::SECONDS_PER_DAY);

		$whereClause = "where timestamp >= '$startDate' and timestamp <= '$endDate'";

		if($_GET['include-all-modules'] === 'true'){
			$whereClause .= ' and external_module_id is not null';
		}

		return $whereClause;
	}

	function getReportData($whereClause, $limitClause){
		$result = $this->query('select * from redcap_external_modules');
		$modulesNamesById = [];
		while($row = db_fetch_assoc($result)){
			$prefix = $row['directory_prefix'];

			// The getConfig() method is not publicly supported, and could change at any time.
			$config = \ExternalModules\ExternalModules::getConfig($prefix);

			$modulesNamesById[$row['external_module_id']] = $config['name'];
		}

		$columns = AnalyticsExternalModule::$COLUMNS;
		$columnNameSql = implode(',', array_keys($columns));
		$sql = "select $columnNameSql";

		$order = $_GET['order'][0];
		$orderColumnName = array_keys($columns)[$order['column']];
		$orderDirection = \db_real_escape_string($order['dir']);

		$result = $this->queryLogs("$sql $whereClause order by $orderColumnName $orderDirection $limitClause");

		$data = [];
		$parametersById = [];
		while($row = db_fetch_assoc($result)){
			$moduleId = $row['external_module_id'];
			$row['external_module_id'] = $modulesNamesById[$moduleId] . " ($moduleId)";

			$logId = $row['log_id'];
			$parameters = [];
			$parametersById[$logId] =& $parameters;
			$row['parameters'] =& $parameters;
			unset($parameters); // required for references above to work properly

			$data[] = $row;
		}

		if(!empty($data)){
			$result = $this->query("select * from redcap_external_modules_log_parameters where log_id in (" . implode(',', array_keys($parametersById)) . ') order by log_id, name desc');
			while($row = db_fetch_assoc($result)){
				$logId = $row['log_id'];
				$name = $row['name'];
				$value = $row['value'];

				$parametersById[$logId][$name] = $value;
			}
		}

		return $data;
	}

	function formatDate($time){
		return date('Y-m-d', $time);
	}
}
