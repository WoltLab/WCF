<?php
namespace wcf\data\search;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes search-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.search
 * @category	Community Framework
 */
class SearchAction extends AbstractDatabaseObjectAction {
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\search\SearchEditor';
}
