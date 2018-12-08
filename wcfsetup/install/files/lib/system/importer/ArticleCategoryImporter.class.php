<?php
namespace wcf\system\importer;
use wcf\data\object\type\ObjectTypeCache;

/**
 * Imports article categories.
 *
 * @author	Marcel Werk
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Importer
 */
class ArticleCategoryImporter extends AbstractCategoryImporter {
	/**
	 * @inheritDoc
	 */
	protected $objectTypeName = 'com.woltlab.wcf.article.category';
	
	/**
	 * Creates a new ArticleCategoryImporter object.
	 */
	public function __construct() {
		$objectType = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.category', 'com.woltlab.wcf.article.category');
		$this->objectTypeID = $objectType->objectTypeID;
	}
}
