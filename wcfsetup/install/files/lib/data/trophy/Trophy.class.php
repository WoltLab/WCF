<?php
namespace wcf\data\trophy;
use wcf\data\condition\Condition;
use wcf\data\trophy\category\TrophyCategory;
use wcf\data\trophy\category\TrophyCategoryCache;
use wcf\data\DatabaseObject;
use wcf\data\ITitledLinkObject;
use wcf\system\condition\ConditionHandler;
use wcf\system\event\EventHandler;
use wcf\system\request\IRouteController;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Represents a user trophy.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Trophy
 * @since	3.1
 *
 * @property-read	integer		$trophyID			unique id for the trophy
 * @property-read	string		$title				the trophy title
 * @property-read	string		$description			the trophy description
 * @property-read	integer		$categoryID			the categoryID of the trophy
 * @property-read	integer		$type				the trophy type
 * @property-read	string		$iconFile			the file location of the icon
 * @property-read	string		$iconName			the icon name
 * @property-read	string		$iconColor			the icon color
 * @property-read	string		$badgeColor			the icon badge color
 * @property-read	integer		$isDisabled			`1` if the trophy is disabled
 * @property-read	integer		$awardAutomatically		`1` if the trophy is awarded automatically
 * @property-read	integer		$trophyUseHtml		        `1`, if the trophy use a html description
 */
class Trophy extends DatabaseObject implements ITitledLinkObject, IRouteController {
	/**
	 * The type value, if this trophy is an image trophy.
	 * @var	integer
	 */
	const TYPE_IMAGE = 1;
	
	/**
	 * The type value, if this trophy is a badge trophy (based on CSS icons).
	 * @var	integer
	 */
	const TYPE_BADGE = 2;
	
	/**
	 * The default icon size. 
	 */
	const DEFAULT_SIZE = 32;
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return WCF::getLanguage()->get($this->title);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		return LinkHandler::getInstance()->getLink('Trophy', [
			'object' => $this,
			'forceFrontend' => true
		]);
	}
	
	/**
	 * Renders a trophy. 
	 * 
	 * @param	integer		$size
	 * @param	boolean		$showTooltip
	 * @return 	string
	 */
	public function renderTrophy($size = self::DEFAULT_SIZE, $showTooltip = false) {
		switch ($this->type) {
			case self::TYPE_IMAGE: {
				return WCF::getTPL()->fetch('trophyImage', 'wcf', [
					'size' => $size,
					'trophy' => $this,
					'showTooltip' => $showTooltip
				], true);
				break;
			}
			
			case self::TYPE_BADGE:
				return WCF::getTPL()->fetch('trophyBadge', 'wcf', [
					'size' => $size,
					'trophy' => $this,
					'showTooltip' => $showTooltip
				], true);
			break;
			
			default: 
				$parameters = [
					'renderedTemplate' => null, 
					'size' => $size,
					'showTooltip' => $showTooltip
				];
				
				EventHandler::getInstance()->fireAction($this, 'renderTrophy', $parameters);
				
				if ($parameters['renderedTemplate']) {
					return $parameters['renderedTemplate']; 
				}
				
				throw new \LogicException("Unable to render the trophy with the type '". $this->type ."'.");
			break; 
		}
	}
	
	/**
	 * Returns the category for this trophy. 
	 * 
	 * @return 	TrophyCategory
	 */
	public function getCategory() {
		return TrophyCategoryCache::getInstance()->getCategoryByID($this->categoryID);
	}
	
	/**
	 * Returns true if the current trophy is disabled. Returns also true if the trophy category is disabled. 
	 * 
	 * @return 	boolean
	 */
	public function isDisabled() {
		if ($this->isDisabled) {
			return true; 
		}
		
		if ($this->getCategory()->isDisabled) {
			return true; 
		}
		
		return false; 
	}
	
	/**
	 * Returns the parsed description for the trophy. 
	 * 
	 * @return 	string
	 */
	public function getDescription() {
		if (!$this->trophyUseHtml) {
			return nl2br(StringUtil::encodeHTML(WCF::getLanguage()->get($this->description)), false);
		}
		
		return WCF::getLanguage()->get($this->description);
	}
	
	/**
	 * Returns the conditions of the trophy.
	 *
	 * @return	Condition[]
	 */
	public function getConditions() {
		return ConditionHandler::getInstance()->getConditions('com.woltlab.wcf.condition.trophy', $this->trophyID);
	}
}
