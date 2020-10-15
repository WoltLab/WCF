<?php

use wcf\system\database\table\column\NotNullVarchar255DatabaseTableColumn;
use wcf\system\database\table\DatabaseTableChangeProcessor;
use wcf\system\database\table\PartialDatabaseTable;
use wcf\system\package\plugin\ScriptPackageInstallationPlugin;
use wcf\system\WCF;

/**
 * Adds the missing database columns that should have been added for 5.3.0 Beta 2 -> 5.3.0 RC 1.
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2020 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core
 */
$tables = [
	PartialDatabaseTable::create('wcf1_article_content')
		->columns([
			NotNullVarchar255DatabaseTableColumn::create('metaTitle')
				->defaultValue(''),
			NotNullVarchar255DatabaseTableColumn::create('metaDescription')
				->defaultValue(''),
		]),
];

(new DatabaseTableChangeProcessor(
/** @var ScriptPackageInstallationPlugin $this */
	$this->installation->getPackage(),
	$tables,
	WCF::getDB()->getEditor())
)->process();
