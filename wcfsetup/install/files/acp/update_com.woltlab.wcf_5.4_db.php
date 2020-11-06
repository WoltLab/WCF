<?php
use wcf\system\database\table\column\BigintDatabaseTableColumn;
use wcf\system\database\table\column\BinaryDatabaseTableColumn;
use wcf\system\database\table\column\NotNullInt10DatabaseTableColumn;
use wcf\system\database\table\column\TextDatabaseTableColumn;
use wcf\system\database\table\DatabaseTable;
use wcf\system\database\table\DatabaseTableChangeProcessor;
use wcf\system\database\table\index\DatabaseTableIndex;
use wcf\system\database\table\index\DatabaseTablePrimaryIndex;
use wcf\system\database\table\PartialDatabaseTable;
use wcf\system\package\plugin\ScriptPackageInstallationPlugin;

$tables = [
	DatabaseTable::create('wcf1_flood_control')
		->columns([
			BigintDatabaseTableColumn::create('logID')
				->length(20)
				->notNull()
				->autoIncrement(),
			NotNullInt10DatabaseTableColumn::create('objectTypeID'),
			BinaryDatabaseTableColumn::create('identifier')
				->length(16)
				->notNull(),
			NotNullInt10DatabaseTableColumn::create('time'),
		])
		->indices([
			DatabaseTablePrimaryIndex::create()
				->columns(['logID']),
			DatabaseTableIndex::create()
				->columns(['identifier']),
			DatabaseTableIndex::create()
				->columns(['time']),
		]),
	
	PartialDatabaseTable::create('wcf1_page_content')
		->columns([
			TextDatabaseTableColumn::create('metaKeywords')
				->drop()
		]),
];

(new DatabaseTableChangeProcessor(
	/** @var ScriptPackageInstallationPlugin $this */
	$this->installation->getPackage(),
	$tables,
	WCF::getDB()->getEditor())
)->process();
