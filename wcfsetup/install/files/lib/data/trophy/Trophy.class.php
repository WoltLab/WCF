<?php
namespace wcf\data\trophy;
use wcf\data\trophy\category\TrophyCategory;
use wcf\data\trophy\category\TrophyCategoryCache;
use wcf\data\DatabaseObject;
use wcf\data\ITitledLinkObject;
use wcf\system\event\EventHandler;
use wcf\system\request\IRouteController;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Represents a user trophy.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Trophy
 * @since	3.1
 *
 * @property-read	integer		$trophyID			unique id for the trophy
 * @property-read	string		$title				the trophy title
 * @property-read	integer		$description			the trophy description
 * @property-read	integer		$categoryID			the categoryID of the trophy
 * @property-read	integer		$type				the trophy type
 * @property-read	string		$iconFile			the file location of the icon
 * @property-read	string		$iconName			the icon name
 * @property-read	string		$iconColor			the icon color
 * @property-read	string		$badgeColor			the icon badge color
 * @property-read	integer		$isDisabled			`1` if the trophy is disabled
 * @property-read	integer		$awardAutomatically		`1` if the trophy is awarded automatically
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
	 * @return 	string
	 */
	public function renderTrophy($size = self::DEFAULT_SIZE) {
		
		switch ($this->type) {
			case self::TYPE_IMAGE: {
				// @TODO
				break;
			}
			
			case self::TYPE_BADGE:
				return WCF::getTPL()->fetch('trophyBadge', 'wcf', [
					'size' => $size,
					'trophy' => $this
				], true);
			break;
			
			default: 
				$parameters = [
					'renderedTemplate' => null, 
					'size' => $size
				];
				
				EventHandler::getInstance()->fireAction($this, 'renderTrophy', $parameters);
				
				if ($parameters['renderedTemplate']) {
					return $parameters['renderedTemplate']; 
				}
				
				// no one has rendered the trophy ; throw an exception
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
		return nl2br(StringUtil::encodeHTML(WCF::getLanguage()->get($this->description)), false); 
	}
}
