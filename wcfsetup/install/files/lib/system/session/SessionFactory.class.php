<?php
namespace wcf\system\session;
use wcf\data\session\SessionEditor;

/**
 * Handles the session of the active user.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.session
 * @category	Community Framework
 */
class SessionFactory extends ACPSessionFactory {
	/**
	 * @inheritDoc
	 */
	protected $cookieSuffix = '';
	
	/**
	 * @inheritDoc
	 */
	protected $sessionEditor = SessionEditor::class;
}
