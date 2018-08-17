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
$orderColumnName = array_keys($columns)[$order['column']];
$orderDirection = \db_real_escape_string($order['dir']);

$offset = \db_real_escape_string($_GET['start']);
$limit = \db_real_escape_string($_GET['length']);

$result = $module->queryLogs("$sql $whereClause order by $orderColumnName $orderDirection limit $limit offset $offset");

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

$result = $module->query("select * from redcap_external_modules_log_parameters where log_id in (" . implode(',', array_keys($parametersById)) . ') order by log_id, name desc');
while($row = db_fetch_assoc($result)){
	$logId = $row['log_id'];
	$name = $row['name'];
	$value = $row['value'];

	$parametersById[$logId][$name] = $value;
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