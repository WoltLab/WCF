<?php
namespace wcf\system\importer;
use wcf\data\category\Category;
use wcf\data\category\CategoryEditor;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\package\Package;
use wcf\data\package\PackageCache;

/**
 * Imports categories.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Importer
 */
class AbstractCategoryImporter extends AbstractImporter {
	/**
	 * @inheritDoc
	 */
	protected $className = Category::class;
	
	/**
	 * object type id for categories
	 * @var	integer
	 */
	protected $objectTypeID = 0;
	
	/**
	 * object type name
	 * @var	integer
	 */
	protected $objectTypeName = '';
	
	/**
	 * @inheritDoc
	 */
	public function import($oldID, array $data, array $additionalData = []) {
		if (!empty($data['parentCategoryID'])) $data['parentCategoryID'] = ImportHandler::getInstance()->getNewID($this->objectTypeName, $data['parentCategoryID']);
		
		$category = CategoryEditor::create(array_merge($data, ['objectTypeID' => $this->objectTypeID]));
		
		// handle i18n values
		if (!empty($additionalData['i18n'])) {
			$values = [];
			
			foreach (['title', 'description'] as $property) {
				if (isset($additionalData['i18n'][$property])) {
					$values[$property] = $additionalData['i18n'][$property];
				}
			}
			
			if (!empty($values)) {
				/** @var Package $package */
				$package = null;
				if ($this->objectTypeID) {
					$objectType = ObjectTypeCache::getInstance()->getObjectType($this->objectTypeID);
					$package = PackageCache::getInstance()->getPackage($objectType->packageID);
				}
				
				if ($package !== null) {
					$updateData = [];
					if (isset($values['title'])) $updateData['title'] = 'wcf.category.category.title.category' . $category->categoryID;
					if (isset($values['description'])) $updateData['description'] = 'wcf.category.category.description.category' . $category->categoryID;
					
					$items = [];
					foreach ($values as $property => $propertyValues) {
						foreach ($propertyValues as $languageID => $languageItemValue) {
							$items[] = [
								'languageID' => $languageID,
								'languageItem' => 'wcf.category.category.' . ($property === 'description' ? 'description' : 'title') . '.category' . $category->categoryID,
								'languageItemValue' => $languageItemValue
							];
						}
					}
					
					$this->importI18nValues($items, 'wcf.category', $package->package);
					
					(new CategoryEditor($category))->update($updateData);
				}
			}
		}
		
		ImportHandler::getInstance()->saveNewID($this->objectTypeName, $oldID, $category->categoryID);
		
		return $category->categoryID;
	}
}
