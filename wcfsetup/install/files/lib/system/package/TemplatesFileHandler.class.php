<?php
namespace wcf\system\package;

/**
 * File handler implementation for the installation of template files.
 * 
 * @author	Benjamin Kunz
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.package
 * @category	Community Framework
 */
class TemplatesFileHandler extends ACPTemplatesFileHandler {
	/**
	 * @see	wcf\system\package\ACPTemplatesFileHandler::$tableName
	 */
	protected $tableName = 'template';
}
