<?php

$whereClause = $module->getReportWhereClause();
$data = $module->getReportData('', '');

$columns = $module::$COLUMNS;
$columns['parameters'] = 'Parameters';

$columnNames = array_keys($columns);
$columnLabels = array_values($columns);

$fp = fopen("php://output",'w');
fputcsv($fp, $columnLabels);
foreach($data as $row){
	$paramText = '';
	foreach($row['parameters'] as $name=>$value){
		$paramText .= "$name: $value\n";
	}

	$row['parameters'] = $paramText;

	fputcsv($fp, array_values($row));
}