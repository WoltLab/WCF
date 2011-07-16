<?php
namespace wcf\data\cleanup\listener;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit cleanup listeners.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.cleanup.listener
 * @category 	Community Framework
 */
class CleanupListenerEditor extends DatabaseObjectEditor {
	/**
	 * @see	wcf\data\DatabaseObjectEditor::$baseClass
	 */
	public $baseClass = 'wcf\data\cleanup\listener\CleanupListener';
}
