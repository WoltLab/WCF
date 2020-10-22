<?php

use wcf\system\database\table\column\MediumblobDatabaseTableColumn;
use wcf\system\database\table\column\NotNullVarchar255DatabaseTableColumn;
use wcf\system\database\table\column\VarcharDatabaseTableColumn;
use wcf\system\database\table\DatabaseTableChangeProcessor;
use wcf\system\database\table\index\DatabaseTableIndex;
use wcf\system\database\table\PartialDatabaseTable;
use wcf\system\WCF;

/**
 * Adjusts wcf1_acp_session and wcf1_acp_session_access_log.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core
 */

$tables = [
	PartialDatabaseTable::create('wcf1_acp_session')
		->columns([
			VarcharDatabaseTableColumn::create('requestURI')->drop(),
			VarcharDatabaseTableColumn::create('requestMethod')->drop(),
			MediumblobDatabaseTableColumn::create('sessionVariables'),
		])
		->indices([
			DatabaseTableIndex::create()
				->columns(['userID']),
			DatabaseTableIndex::create()
				->columns(['lastActivityTime']),
		]),
	PartialDatabaseTable::create('wcf1_acp_session_access_log')
		->columns([
			NotNullVarchar255DatabaseTableColumn::create('requestMethod')
				->defaultValue(''),
		])
];

(new DatabaseTableChangeProcessor(
/** @var ScriptPackageInstallationPlugin $this */
	$this->installation->getPackage(),
	$tables,
	WCF::getDB()->getEditor())
)->process();
