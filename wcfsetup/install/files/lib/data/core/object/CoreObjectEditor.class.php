<?php
namespace wcf\data\core\object;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit core objects.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.core.object
 * @category	Community Framework
 */
class CoreObjectEditor extends DatabaseObjectEditor {
	/**
	 * @see	\wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\core\object\CoreObject';
}
