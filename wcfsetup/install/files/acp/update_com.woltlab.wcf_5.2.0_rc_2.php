<?php
use wcf\system\database\table\column\NotNullInt10DatabaseTableColumn;
use wcf\system\database\table\DatabaseTableChangeProcessor;
use wcf\system\database\table\PartialDatabaseTable;
use wcf\system\package\plugin\ScriptPackageInstallationPlugin;
use wcf\system\WCF;

/**
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
$tables = [
	PartialDatabaseTable::create('wcf1_media')
		->columns([
			// Incorrect column name in the upgrade 3.1 -> 5.2.0 RC 1
			NotNullInt10DatabaseTableColumn::create('lastDownload')
				->defaultValue(0)
				->drop(),
			NotNullInt10DatabaseTableColumn::create('lastDownloadTime')
				->defaultValue(0)
		]),
];

(new DatabaseTableChangeProcessor(
	/** @var ScriptPackageInstallationPlugin $this */
	$this->installation->getPackage(),
	$tables,
	WCF::getDB()->getEditor())
)->process();
