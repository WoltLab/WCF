<?php
namespace wcf\system\importer;
use wcf\data\object\type\ObjectTypeCache;

/**
 * Imports smiley categories.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.importer
 * @category	Community Framework
 */
class SmileyCategoryImporter extends AbstractCategoryImporter {
	/**
	 * @see	\wcf\system\importer\AbstractCommentImporter::$objectTypeName
	 */
	protected $objectTypeName = 'com.woltlab.wcf.smiley.category';
	
	/**
	 * Creates a new SmileyCategoryImporter object.
	 */
	public function __construct() {
		$objectType = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.category', 'com.woltlab.wcf.bbcode.smiley');
		$this->objectTypeID = $objectType->objectTypeID;
	}
}
