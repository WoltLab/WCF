<?php

use wcf\system\database\table\DatabaseTableChangeProcessor;
use wcf\system\database\table\PartialDatabaseTable;
use wcf\system\WCF;

/**
 * Removes the *_session_virtual tables.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core
 */

$tables = [
	PartialDatabaseTable::create('wcf1_session_virtual')
		->drop(),
	PartialDatabaseTable::create('wcf1_acp_session_virtual')
		->drop(),
];

(new DatabaseTableChangeProcessor(
/** @var ScriptPackageInstallationPlugin $this */
	$this->installation->getPackage(),
	$tables,
	WCF::getDB()->getEditor())
)->process();
