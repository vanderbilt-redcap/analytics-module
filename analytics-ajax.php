<?php
namespace Vanderbilt\AnalyticsExternalModule;

use ExternalModules\ExternalModules;

$result = $module->query('select * from redcap_external_modules');
$modulesNamesById = [];
while($row = db_fetch_assoc($result)){
	$prefix = $row['directory_prefix'];

	// The getConfig() method is not publicly supported, and could change at any time.
	$config = ExternalModules::getConfig($prefix);

	$modulesNamesById[$row['external_module_id']] = $config['name'];
}

$columns = AnalyticsExternalModule::COLUMNS;
$columnNameSql = implode(',', array_keys($columns));
$sql = "select $columnNameSql";

$whereClause = '';

$order = $_GET['order'][0];
$orderColumnIndex = \db_real_escape_string($order['column']);
$orderDirection = \db_real_escape_string($order['dir']);
$orderColumnName = \db_real_escape_string($_GET['columns'][$orderColumnIndex]['name']);

$offset = \db_real_escape_string($_GET['start']);
$limit = \db_real_escape_string($_GET['length']);

$result = $module->queryLogs("$sql $whereClause order by $orderColumnName $orderDirection limit $limit offset $offset");

$data = [];
while($row = db_fetch_assoc($result)){
	$dataRow = [];

	foreach($columns as $name=>$label){
		$value = $row[$name];

		if($name === 'external_module_id'){
			$value = $modulesNamesById[$value] . " ($value)";
		}

		$dataRow[] = $value;
	}

	$data[] = $dataRow;
}

$columnName = 'count(1)';
$result = $module->queryLogs("select $columnName $whereClause");
$row = db_fetch_assoc($result);
$totalRowCount = $row[$columnName];

?>

{
	"draw": <?=$_GET['draw']?>,
	"recordsTotal": <?=$totalRowCount?>,
	"recordsFiltered": <?=$totalRowCount?>,
	"data": <?=json_encode($data)?>
}