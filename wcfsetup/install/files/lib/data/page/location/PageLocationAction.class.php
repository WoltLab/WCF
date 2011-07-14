<?php
namespace wcf\data\page\location;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes page location-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.page.location
 * @category 	Community Framework
 */
class PageLocationAction extends AbstractDatabaseObjectAction {
	/**
	 * @see AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\page\location\PageLocationEditor';
}
