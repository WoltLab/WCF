<?php
namespace wcf\system\importer;

/**
 * Basic implementation of IImporter.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Importer
 */
abstract class AbstractImporter implements IImporter {
	/**
	 * database object class name
	 * @var	string
	 */
	protected $className = '';
	
	/**
	 * @inheritDoc
	 */
	public function getClassName() {
		return $this->className;
	}
}
