<?php
namespace wcf\data\page;
use wcf\data\language\Language;
use wcf\system\language\LanguageFactory;
use wcf\system\request\LinkHandler;

/**
 * Represents a page language.
 *
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Page
 * @since	3.0
 */
class PageLanguage {
	/**
	 * language id
	 * @var integer
	 */
	protected $languageID;
	
	/**
	 * page id
	 * @var integer
	 */
	protected $pageID;
	
	/**
	 * Creates a new PageLanguage object.
	 *
	 * @param       integer         $pageID
	 * @param       integer         $languageID
	 */
	public function __construct($pageID, $languageID) {
		$this->pageID = $pageID;
		$this->languageID = $languageID;
	}
	
	/**
	 * Returns the link to this version of the page.
	 *
	 * @return	string
	 */
	public function getLink() {
		return LinkHandler::getInstance()->getCmsLink($this->pageID, $this->languageID);
	}
	
	/**
	 * Returns the language of this version of the page.
	 *
	 * @return	Language
	 */
	public function getLanguage() {
		return LanguageFactory::getInstance()->getLanguage($this->languageID);
	}
}
