<?php
namespace wcf\data\application;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit applications.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.application
 * @category 	Community Framework
 */
class ApplicationEditor extends DatabaseObjectEditor {
	/**
	 * @see	DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\application\Application';
}
