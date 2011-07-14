<?php
namespace wcf\system\package;

/**
 * TemplatesFileHandler is a FileHandler implementation for the installation of template files.
 * 
 * @author	Benjamin Kunz
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.package
 * @category 	Community Framework
 */
class TemplatesFileHandler extends ACPTemplatesFileHandler {
	protected $tableName = '_template';
}
?>