<?php
namespace wcf\data\user\rank;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit user ranks.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.user
 * @subpackage	data.user.rank
 * @category	Community Framework
 */
class UserRankEditor extends DatabaseObjectEditor {
	/**
	 * @see	wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\user\rank\UserRank';
}
