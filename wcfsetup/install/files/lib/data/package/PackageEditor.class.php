<?php
namespace wcf\data\package;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit packages.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.package
 * @category 	Community Framework
 */
class PackageEditor extends DatabaseObjectEditor {
	/**
	 * @see	DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\package\Package';
}
