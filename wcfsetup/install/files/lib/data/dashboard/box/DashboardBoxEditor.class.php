<?php
namespace wcf\data\dashboard\box;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit dashboard boxes.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.dashboard.box
 * @category	Community Framework
 */
class DashboardBoxEditor extends DatabaseObjectEditor {
	/**
	 * @see	\wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\dashboard\box\DashboardBox';
}
