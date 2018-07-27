<?php
namespace wcf\acp\page;
use wcf\data\box\Box;
use wcf\data\page\Page;
use wcf\data\page\PageCache;
use wcf\page\AbstractPage;
use wcf\system\box\BoxHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

/**
 * Shows the list of boxes for selected page and offers sorting capabilities.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Page
 * @since	3.0
 */
class PageBoxOrderPage extends AbstractPage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.cms.page.list';
	
	/**
	 * list of boxes by position
	 * @var Box[][]
	 */
	public $boxes;
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.content.cms.canManagePage'];
	
	/**
	 * page object
	 * @var Page
	 */
	public $page;
	
	/**
	 * page id
	 * @var integer
	 */
	public $pageID = 0;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (!empty($_REQUEST['id'])) $this->pageID = intval($_REQUEST['id']);
		
		$this->page = PageCache::getInstance()->getPage($this->pageID);
		if (!$this->page->pageID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		$this->boxes = BoxHandler::loadBoxes($this->pageID, false);
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'boxes' => $this->boxes,
			'hasCustomShowOrder' => BoxHandler::hasCustomShowOrder($this->pageID),
			'page' => $this->page,
			'pageID' => $this->pageID
		]);
	}
}
