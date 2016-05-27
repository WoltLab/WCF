<?php
namespace wcf\acp\page;
use wcf\data\box\Box;
use wcf\data\box\BoxList;
use wcf\page\SortablePage;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows a list of boxes.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category	Community Framework
 * @since	2.2
 * 
 * @property	BoxList		$objectList
 */
class BoxListPage extends SortablePage {
	/**
	 * @inheritdoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.cms.box.list';
	
	/**
	 * @inheritdoc
	 */
	public $objectListClassName = BoxList::class;
	
	/**
	 * @inheritdoc
	 */
	public $neededPermissions = ['admin.content.cms.canManageBox'];
	
	/**
	 * @inheritdoc
	 */
	public $defaultSortField = 'name';
	
	/**
	 * @inheritdoc
	 */
	public $validSortFields = ['boxID', 'name', 'boxType', 'position', 'showOrder'];
	
	/**
	 * name
	 * @var	string
	 */
	public $name = '';
	
	/**
	 * title
	 * @var	string
	 */
	public $title = '';
	
	/**
	 * content
	 * @var	string
	 */
	public $content = '';
	
	/**
	 * box type
	 * @var string
	 */
	public $boxType = 'static';
	
	/**
	 * box position
	 * @var string
	 */
	public $position = '';
	
	/**
	 * display 'Add Box' dialog on load
	 * @var integer
	 */
	public $showBoxAddDialog = 0;
	
	/**
	 * @inheritdoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (!empty($_REQUEST['name'])) $this->name = StringUtil::trim($_REQUEST['name']);
		if (!empty($_REQUEST['title'])) $this->title = StringUtil::trim($_REQUEST['title']);
		if (!empty($_REQUEST['content'])) $this->content = StringUtil::trim($_REQUEST['content']);
		if (!empty($_REQUEST['boxType'])) $this->boxType = $_REQUEST['boxType'];
		if (!empty($_REQUEST['position'])) $this->position = $_REQUEST['position'];
		if (!empty($_REQUEST['showBoxAddDialog'])) $this->showBoxAddDialog = 1;
	}
	
	/**
	 * @inheritdoc
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		// hide menu boxes
		$this->objectList->getConditionBuilder()->add('box.boxType <> ?', ['menu']);
		
		if (!empty($this->name)) {
			$this->objectList->getConditionBuilder()->add('box.name LIKE ?', ['%'.$this->name.'%']);
		}
		if (!empty($this->title)) {
			$this->objectList->getConditionBuilder()->add('box.boxID IN (SELECT boxID FROM wcf'.WCF_N.'_box_content WHERE title LIKE ?)', ['%'.$this->title.'%']);
		}
		if (!empty($this->content)) {
			$this->objectList->getConditionBuilder()->add('box.boxID IN (SELECT boxID FROM wcf'.WCF_N.'_box_content WHERE content LIKE ?)', ['%'.$this->content.'%']);
		}
		if (!empty($this->position)) {
			$this->objectList->getConditionBuilder()->add('box.position = ?', [$this->position]);
		}
		if ($this->boxType == 'static') {
			$this->objectList->getConditionBuilder()->add('box.boxType IN (?, ?, ?)', ['text', 'html', 'tpl']);
		}
		else if ($this->boxType == 'system') {
			$this->objectList->getConditionBuilder()->add('box.boxType IN (?)', ['system']);
		}
	}
	
	/**
	 * @inheritdoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'name' => $this->name,
			'title' => $this->title,
			'content' => $this->content,
			'boxType' => $this->boxType,
			'position' => $this->position,
			'availablePositions' => Box::$availablePositions,
			'showBoxAddDialog' => $this->showBoxAddDialog
		]);
	}
}
