<?php
namespace wcf\data\style\variable;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to create, edit and delete a style variable.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.style.variable
 * @category	Community Framework
 */
class StyleVariableEditor extends DatabaseObjectEditor {
	/**
	 * @see	\wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\style\variable\StyleVariable';
}
