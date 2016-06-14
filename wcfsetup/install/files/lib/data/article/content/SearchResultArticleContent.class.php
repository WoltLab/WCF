<?php
namespace wcf\data\article\content;
use wcf\data\search\ISearchResultObject;
use wcf\system\request\LinkHandler;
use wcf\system\search\SearchResultTextParser;

/**
 * Represents an article content as a search result.
 *
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Article\Content
 * @since	3.0
 */
class SearchResultArticleContent extends ViewableArticleContent implements ISearchResultObject {
	/**
	 * @inheritDoc
	 */
	public function getUserProfile() {
		return $this->getArticle()->getUserProfile();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getSubject() {
		return $this->getDecoratedObject()->getTitle();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTime() {
		return $this->getArticle()->time;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink($query = '') {
		$parameters = [
			'object' => $this->getDecoratedObject(),
			'forceFrontend' => true
		];
		
		if ($query) {
			$parameters['highlight'] = urlencode($query);
		}
		
		return LinkHandler::getInstance()->getLink('Article', $parameters);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getObjectTypeName() {
		return 'com.woltlab.wcf.article';
	}
	
	/**
	 * @inheritDoc
	 */
	public function getFormattedMessage() {
		// @todo
		$message = SearchResultTextParser::getInstance()->parse($this->getDecoratedObject()->getFormattedContent());
		
		if ($this->getImage()) {
			return '<div class="box96">'.$this->getImage()->getElementTag(96).'<div>'.$message.'</div></div>';
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
