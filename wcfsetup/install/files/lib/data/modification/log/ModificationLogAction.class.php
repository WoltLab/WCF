<?php
namespace wcf\data\modification\log;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes modification log-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Modification\Log
 * 
 * @method	ModificationLog			create()
 * @method	ModificationLogEditor[]		getObjects()
 * @method	ModificationLogEditor		getSingleObject()
 */
class ModificationLogAction extends AbstractDatabaseObjectAction { }
