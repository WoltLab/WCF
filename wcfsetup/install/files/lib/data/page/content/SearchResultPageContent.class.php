<?php
namespace wcf\data\page\content;
use wcf\data\search\ISearchResultObject;
use wcf\data\DatabaseObjectDecorator;
use wcf\system\request\LinkHandler;
use wcf\system\search\SearchResultTextParser;

/**
 * Represents an page content as a search result.
 *
 * @author	Marcel Werk
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Page\Content
 * @since	3.1
 *        
 * @method	PageContent	getDecoratedObject()
 * @mixin	PageContent
 */
class SearchResultPageContent extends DatabaseObjectDecorator implements ISearchResultObject {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = PageContent::class;
	
	/**
	 * @inheritDoc
	 */
	public function getUserProfile() {
		return null;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getSubject() {
		return $this->getDecoratedObject()->title;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTime() {
		return 0;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink($query = '') {
		return LinkHandler::getInstance()->getCmsLink($this->getDecoratedObject()->pageID, ($this->getDecoratedObject()->languageID ?: -1));
	}
	
	/**
	 * @inheritDoc
	 */
	public function getObjectTypeName() {
		return 'com.woltlab.wcf.page';
	}
	
	/**
	 * @inheritDoc
	 */
	public function getFormattedMessage() {
		if ($this->getDecoratedObject()->pageType == 'text') {
			$message = SearchResultTextParser::getInstance()->parse($this->getDecoratedObject()->getFormattedContent());
		}
		else {
			$message = SearchResultTextParser::getInstance()->parse($this->getDecoratedObject()->getParsedContent());
		}
		
		return $message;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getContainerTitle() {
		return '';
	}
	
	/**
	 * @inheritDoc
	 */
	public function getContainerLink() {
		return '';
	}
}
