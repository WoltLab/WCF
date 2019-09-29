<?php
use wcf\system\database\table\column\{
	DefaultFalseBooleanDatabaseTableColumn,
	DefaultTrueBooleanDatabaseTableColumn,
	NotNullVarchar255DatabaseTableColumn
};
use wcf\system\database\table\DatabaseTable;
use wcf\system\database\table\DatabaseTableChangeProcessor;
use wcf\system\package\plugin\ScriptPackageInstallationPlugin;
use wcf\system\WCF;

/**
 * Updates the database table layout from WoltLab Suite Core 5.2.0 Beta 1 to 5.2.0 Beta 2
 * 
 * @author	Alexander Ebert, Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

$tables = [
	DatabaseTable::create('wcf1_reaction_type')
		->columns([
			NotNullVarchar255DatabaseTableColumn::create('title'),
			NotNullVarchar255DatabaseTableColumn::create('iconFile')
				->defaultValue(''),
			DefaultTrueBooleanDatabaseTableColumn::create('isAssignable'),
			
			DefaultFalseBooleanDatabaseTableColumn::create('isDisabled')
				->drop()
		]),
];

(new DatabaseTableChangeProcessor(
	/** @var ScriptPackageInstallationPlugin $this */
	$this->installation->getPackage(),
	$tables,
	WCF::getDB()->getEditor())
)->process();
