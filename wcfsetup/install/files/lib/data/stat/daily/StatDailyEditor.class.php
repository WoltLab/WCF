<?php
namespace wcf\data\stat\daily;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to create, edit and delete a stat daily entry.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.stat.daily
 * @category	Community Framework
 */
class StatDailyEditor extends DatabaseObjectEditor {
	/**
	 * @see	\wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\stat\daily\StatDaily';
}
