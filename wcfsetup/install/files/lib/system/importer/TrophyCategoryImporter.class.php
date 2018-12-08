<?php
namespace wcf\system\importer;
use wcf\data\object\type\ObjectTypeCache;

/**
 * Represents a trophy category importer.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Importer
 * @since	3.1
 */
class TrophyCategoryImporter extends AbstractCategoryImporter {
	/**
	 * @inheritDoc
	 */
	protected $objectTypeName = 'com.woltlab.wcf.trophy.category';
	
	/**
	 * Creates a new TrophyCategoryImporter object.
	 */
	public function __construct() {
		$objectType = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.category', $this->objectTypeName);
		$this->objectTypeID = $objectType->objectTypeID;
	}
}
