<?php
use wcf\system\database\table\DatabaseTableChangeProcessor;
use wcf\system\database\table\index\DatabaseTableIndex;
use wcf\system\database\table\PartialDatabaseTable;
use wcf\system\package\plugin\ScriptPackageInstallationPlugin;
use wcf\system\WCF;

/**
 * Updates the database table layout from WoltLab Suite Core 5.2.0 Beta 2 to 5.2.0 Beta 3.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

$tables = [
	PartialDatabaseTable::create('wcf1_package')
		->indices([
			DatabaseTableIndex::create('package')
				->columns(['package'])
				->drop(),
			
			DatabaseTableIndex::create()
				->type(DatabaseTableIndex::UNIQUE_TYPE)
				->columns(['package'])
		])
];

(new DatabaseTableChangeProcessor(
/** @var ScriptPackageInstallationPlugin $this */
	$this->installation->getPackage(),
	$tables,
	WCF::getDB()->getEditor())
)->process();
