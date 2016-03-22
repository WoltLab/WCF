<?php
namespace wcf\page;
use wcf\data\page\Page;
use wcf\system\exception\IllegalLinkException;
use wcf\system\request\LinkHandler;
use wcf\system\request\RequestHandler;
use wcf\system\WCF;

/**
 * Generic controller to display cms content.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	page
 * @category	Community Framework
 * @since	2.2
 */
class CmsPage extends AbstractPage {
	/**
	 * @var string[]
	 */
	public $content;
	
	/**
	 * @var integer
	 */
	public $languageID;
	
	/**
	 * @var Page
	 */
	public $page;
	
	/**
	 * @var integer
	 */
	public $pageID;
	
	/**
	 * @inheritDoc
	 * @throws      IllegalLinkException
	 */
	public function readParameters() {
		parent::readParameters();
		
		$metaData = RequestHandler::getInstance()->getActiveRequest()->getMetaData();
		if (isset($metaData['cms'], $metaData['cms']['pageID'])) {
			$this->pageID = $metaData['cms']['pageID'];
			
			if (isset($metaData['cms']['languageID'])) $this->languageID = $metaData['cms']['languageID'];
		}
		
		if ($this->pageID) {
			$this->page = new Page($this->pageID);
		}
		
		if ($this->page === null) {
			throw new IllegalLinkException();
		}
		
		$this->content = $this->page->getPageContentByLanguage($this->languageID);
		if (empty($this->content)) {
			throw new IllegalLinkException();
		}
		
		$this->canonicalURL = LinkHandler::getInstance()->getCmsLink($this->pageID, $this->languageID);
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'canonicalURL' => $this->canonicalURL,
			'content' => $this->content,
			'contentLanguageID' => $this->languageID,
			'page' => $this->page,
			'pageID' => $this->pageID
		]);
	}
}
