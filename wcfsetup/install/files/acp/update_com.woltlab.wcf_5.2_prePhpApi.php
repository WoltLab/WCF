<?php
use wcf\system\WCF;

// This script ensures that an upgrade that failed during the SQL changes can be repeated without requiring a rollback.

$dbEditor = WCF::getDB()->getEditor();
$existingColumns = $dbEditor->getColumns('wcf'.WCF_N.'_package_installation_sql_log');
$hasIsDoneColumn = false;
foreach ($existingColumns as $column) {
	if ($column['name'] === 'isDone') {
		$hasIsDoneColumn = true;
		break;
	}
}

if (!$hasIsDoneColumn) {
	$dbEditor->addColumn('wcf'.WCF_N.'_package_installation_sql_log', 'isDone', [
		'type' => 'tinyint',
		'length' => 1,
		'notNull' => 1,
		'default' => 1,
	]);
}
