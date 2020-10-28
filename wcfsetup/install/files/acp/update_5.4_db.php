<?php
use wcf\system\database\table\column\TextDatabaseTableColumn;
use wcf\system\database\table\DatabaseTableChangeProcessor;
use wcf\system\database\table\PartialDatabaseTable;
use wcf\system\package\plugin\ScriptPackageInstallationPlugin;

$tables = [
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
