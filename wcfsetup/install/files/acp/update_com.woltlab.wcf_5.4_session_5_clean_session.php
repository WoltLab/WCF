<?php

use wcf\system\database\table\column\MediumblobDatabaseTableColumn;
use wcf\system\database\table\column\NotNullVarchar255DatabaseTableColumn;
use wcf\system\database\table\column\VarcharDatabaseTableColumn;
use wcf\system\database\table\DatabaseTableChangeProcessor;
use wcf\system\database\table\index\DatabaseTableIndex;
use wcf\system\database\table\PartialDatabaseTable;
use wcf\system\WCF;

/**
 * Adjusts wcf1_acp_session_access_log and wcf1_session.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core
 */

$tables = [
	PartialDatabaseTable::create('wcf1_acp_session_access_log')
		->columns([
			NotNullVarchar255DatabaseTableColumn::create('requestMethod')
				->defaultValue(''),
		]),
	PartialDatabaseTable::create('wcf1_session')
		->columns([
			MediumblobDatabaseTableColumn::create('sessionVariables')->drop(),
		]),
];

(new DatabaseTableChangeProcessor(
/** @var ScriptPackageInstallationPlugin $this */
	$this->installation->getPackage(),
	$tables,
	WCF::getDB()->getEditor())
)->process();
