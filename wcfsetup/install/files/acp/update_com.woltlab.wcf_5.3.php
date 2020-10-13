<?php

use wcf\system\database\table\column\CharDatabaseTableColumn;
use wcf\system\database\table\column\IntDatabaseTableColumn;
use wcf\system\database\table\column\MediumtextDatabaseTableColumn;
use wcf\system\database\table\column\NotNullInt10DatabaseTableColumn;
use wcf\system\database\table\column\NotNullVarchar191DatabaseTableColumn;
use wcf\system\database\table\column\NotNullVarchar255DatabaseTableColumn;
use wcf\system\database\table\column\ObjectIdDatabaseTableColumn;
use wcf\system\database\table\DatabaseTable;
use wcf\system\database\table\DatabaseTableChangeProcessor;
use wcf\system\database\table\PartialDatabaseTable;
use wcf\system\database\table\index\DatabaseTableForeignKey;
use wcf\system\database\table\index\DatabaseTableIndex;
use wcf\system\package\plugin\ScriptPackageInstallationPlugin;
use wcf\system\WCF;

/**
 * Updates the database table layout from WoltLab Suite Core 5.2 to 5.3.
 * 
 * @author	Florian Gail
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core
 */

$tables = [
	PartialDatabaseTable::create('wcf1_article_content')
		->columns([
			NotNullVarchar255DatabaseTableColumn::create('metaTitle')
				->defaultValue(''),
			NotNullVarchar255DatabaseTableColumn::create('metaDescription')
				->defaultValue(''),
		]),
	DatabaseTable::create('wcf1_devtools_missing_language_item')
		->columns([
			ObjectIdDatabaseTableColumn::create('itemID'),
			IntDatabaseTableColumn::create('languageID'),
			NotNullVarchar191DatabaseTableColumn::create('languageItem'),
			NotNullInt10DatabaseTableColumn::create('lastTime'),
			MediumtextDatabaseTableColumn::create('stackTrace')
				->notNull()
		])
		->indices([
			DatabaseTableIndex::create()
				->type(DatabaseTableIndex::UNIQUE_TYPE)
				->columns(['languageID', 'languageItem']),
		])
		->foreignKeys([
			DatabaseTableForeignKey::create()
				->columns(['languageID'])
				->referencedTable('wcf1_language')
				->referencedColumns(['languageID'])
				->onDelete('SET NULL')
		]),
	PartialDatabaseTable::create('wcf1_media')
		->columns([
			NotNullInt10DatabaseTableColumn::create('fileUpdateTime')
				->defaultValue(0)
		]),
	PartialDatabaseTable::create('wcf1_user')
		->columns([
			CharDatabaseTableColumn::create('emailConfirmed')
				->length(40),
			NotNullVarchar255DatabaseTableColumn::create('password')
				->defaultValue('invalid:'),
		])
		->indices([
			DatabaseTableIndex::create('username')
				->type(DatabaseTableIndex::UNIQUE_TYPE)
				->columns(['username']),
		]),
];

(new DatabaseTableChangeProcessor(
/** @var ScriptPackageInstallationPlugin $this */
	$this->installation->getPackage(),
	$tables,
	WCF::getDB()->getEditor())
)->process();
