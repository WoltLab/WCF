<?php
namespace wcf\data\acp\session;
use wcf\data\DatabaseObjectEditor;
use wcf\system\WCF;

/**
 * Provides functions to edit ACP sessions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.acp.session
 * @category 	Community Framework
 */
class ACPSessionEditor extends DatabaseObjectEditor {
	/**
	 * @see	wcf\data\DatabaseObjectEditor::$baseClass
	 */
	protected static $baseClass = 'wcf\data\acp\session\ACPSession';
	
	/**
	 * @see	wcf\data\DatabaseObjectEditor::create()
	 */
	public static function create(array $parameters = array()) {
		if (isset($parameters['userID']) && !$parameters['userID']) {
			$parameters['userID'] = null;
		}
		if (isset($parameters['packageID']) && !$parameters['packageID']) {
			$parameters['packageID'] = null;
		}
		
		return parent::create($parameters);
	}
	
	/**
	 * @see	wcf\data\DatabaseObjectEditor::create()
	 */
	public function update(array $parameters = array()) {
		if (isset($parameters['userID']) && !$parameters['userID']) {
			$parameters['userID'] = null;
		}
		if (isset($parameters['packageID']) && !$parameters['packageID']) {
			$parameters['packageID'] = null;
		}
		
		return parent::update($parameters);
	}
}
