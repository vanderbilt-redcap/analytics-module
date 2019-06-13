<?php
namespace Vanderbilt\AnalyticsExternalModule;

use ExternalModules\ExternalModules;

$whereClause = $module->getReportWhereClause();

$offset = \db_real_escape_string($_GET['start']);
$limit = \db_real_escape_string($_GET['length']);
$limitClause = "limit $limit offset $offset";

$columnName = 'count(1)';
$result = $module->queryLogs("select $columnName $whereClause");
$row = db_fetch_assoc($result);
$totalRowCount = $row[$columnName];

?>

{
	"draw": <?=$_GET['draw']?>,
	"recordsTotal": <?=$totalRowCount?>,
	"recordsFiltered": <?=$totalRowCount?>,
	"data": <?=json_encode($module->getReportData($whereClause, $limitClause))?>
}