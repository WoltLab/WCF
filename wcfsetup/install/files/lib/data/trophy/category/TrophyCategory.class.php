<?php
namespace wcf\data\trophy\category;
use wcf\data\category\AbstractDecoratedCategory;
use wcf\data\trophy\Trophy;
use wcf\data\trophy\TrophyCache;
use wcf\data\user\User;
use wcf\data\ITitledLinkObject;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Represents a trophy category.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Trophy\Category
 * @since	3.1
 *
 * @method		TrophyCategory[]	getChildCategories()
 * @method		TrophyCategory[]	getAllChildCategories()
 * @method		TrophyCategory		getParentCategory()
 * @method		TrophyCategory[]	getParentCategories()
 * @method static	TrophyCategory|null	getCategory($categoryID)
 */
class TrophyCategory extends AbstractDecoratedCategory implements ITitledLinkObject {
	/**
	 * object type name of the trophy categories
	 * @var	string
	 */
	const OBJECT_TYPE_NAME = 'com.woltlab.wcf.trophy.category';
	
	/**
	 * @inheritDoc
	 */
	public function isAccessible(User $user = null) {
		if ($this->getObjectType()->objectType != self::OBJECT_TYPE_NAME) return false;
		
		if ($this->getDecoratedObject()->isDisabled) {
			return false; 
		}
		
		return true; 
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		return LinkHandler::getInstance()->getLink('CategoryTrophyList', [
			'forceFrontend' => true,
			'object' => $this->getDecoratedObject()
		]);
	}
	
	/** @noinspection PhpMissingParentCallCommonInspection */
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return WCF::getLanguage()->get($this->title);
	}
	
	/**
	 * Returns the trophies for the category. 
	 * 
	 * @param 	boolean 	$includeDisabled 
	 * @return 	Trophy[]
	 */
	public function getTrophies($includeDisabled = false) {
		if ($includeDisabled) {
			return TrophyCache::getInstance()->getTrophiesByCategoryID($this->getObjectID()); 
		}
		
		return TrophyCache::getInstance()->getEnabledTrophiesByCategoryID($this->getObjectID());
	}
}
