<?php
namespace wcf\system\importer;
use wcf\data\user\option\category\UserOptionCategoryEditor;
use wcf\data\user\option\category\UserOptionCategoryList;
use wcf\data\user\option\UserOption;
use wcf\data\user\option\UserOptionAction;
use wcf\data\user\option\UserOptionEditor;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Imports user options.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Importer
 */
class UserOptionImporter extends AbstractImporter {
	/**
	 * @inheritDoc
	 */
	protected $className = UserOption::class;
	
	/**
	 * language category id
	 * @var	integer
	 */
	protected $languageCategoryID = null;
	
	/**
	 * list of available user option categories
	 * @var	string[]
	 */
	protected $categoryCache = null;
	
	/**
	 * Creates a new UserOptionImporter object.
	 */
	public function __construct() {
		// get language category id
		$sql = "SELECT	languageCategoryID
			FROM	wcf".WCF_N."_language_category
			WHERE	languageCategory = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(['wcf.user.option']);
		$row = $statement->fetchArray();
		$this->languageCategoryID = $row['languageCategoryID'];
	}
	
	/**
	 * @inheritDoc
	 */
	public function import($oldID, array $data, array $additionalData = []) {
		$data['packageID'] = 1;
		// set temporary option name
		$data['optionName'] = StringUtil::getRandomID();
		
		if ($data['optionType'] == 'boolean' || $data['optionType'] == 'integer') {
			if (isset($data['defaultValue'])) {
				$data['defaultValue'] = intval($data['defaultValue']);
			}
		}
		
		// create category
		$this->createCategory($data['categoryName']);
		
		// save option
		$action = new UserOptionAction([], 'create', ['data' => $data]);
		$returnValues = $action->executeAction();
		$userOption = $returnValues['returnValues'];
		
		// update generic option name
		$editor = new UserOptionEditor($userOption);
		$editor->update([
			'optionName' => 'option'.$userOption->optionID
		]);
		
		// save name
		$sql = "INSERT IGNORE INTO	wcf".WCF_N."_language_item
						(languageID, languageItem, languageItemValue, languageItemOriginIsSystem, languageCategoryID, packageID)
			VALUES			(?, ?, ?, ?, ?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			LanguageFactory::getInstance()->getDefaultLanguageID(),
			'wcf.user.option.option'.$userOption->optionID,
			$additionalData['name'],
			0,
			$this->languageCategoryID,
			1
		]);
		
		ImportHandler::getInstance()->saveNewID('com.woltlab.wcf.user.option', $oldID, $userOption->optionID);
		
		return $userOption->optionID;
	}
	
	/**
	 * Creates the given category if necessary.
	 * 
	 * @param	string		$name
	 */
	protected function createCategory($name) {
		if ($this->categoryCache === null) {
			// get existing categories
			$list = new UserOptionCategoryList();
			$list->getConditionBuilder()->add('categoryName = ? OR parentCategoryName = ?', ['profile', 'profile']);
			$list->readObjects();
			foreach ($list->getObjects() as $category) $this->categoryCache[] = $category->categoryName;
		}
		
		if (!in_array($name, $this->categoryCache)) {
			// create category
			UserOptionCategoryEditor::create([
				'packageID' => 1,
				'categoryName' => $name,
				'parentCategoryName' => 'profile'
			]);
			
			$this->categoryCache[] = $name;
		}
	}
}
