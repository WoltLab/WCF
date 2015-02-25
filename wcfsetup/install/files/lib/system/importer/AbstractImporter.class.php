<?php
namespace wcf\system\importer;

/**
 * Basic implementation of IImporter.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.importer
 * @category	Community Framework
 */
abstract class AbstractImporter implements IImporter {
	/**
	 * database object class name
	 * @var	string
	 */
	protected $className = '';
	
	/**
	 * @see	\wcf\system\importer\IImporter::getClassName()
	 */
	public function getClassName() {
		return $this->className;
	}
}
