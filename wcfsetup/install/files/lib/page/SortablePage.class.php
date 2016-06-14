<?php
namespace wcf\page;
use wcf\system\event\EventHandler;
use wcf\system\WCF;

/**
 * Provides default implementations for a sortable page of listed items.
 * Handles the sorting parameters automatically.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Page
 */
abstract class SortablePage extends MultipleLinkPage {
	/**
	 * default sort field
	 * @var	string
	 */
	public $defaultSortField = '';
	
	/**
	 * default sort order
	 * @var	string
	 */
	public $defaultSortOrder = 'ASC';
	
	/**
	 * list of valid sort fields
	 * @var	string[]
	 */
	public $validSortFields = [];
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		// read sorting parameter
		if (isset($_REQUEST['sortField'])) $this->sortField = $_REQUEST['sortField'];
		if (isset($_REQUEST['sortOrder'])) $this->sortOrder = $_REQUEST['sortOrder'];
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		$this->validateSortOrder();
		$this->validateSortField();
				
		parent::readData();
	}
	
	/**
	 * Validates the given sort field parameter. 
	 */
	public function validateSortField() {
		// call validateSortField event
		EventHandler::getInstance()->fireAction($this, 'validateSortField');
		
		if (!in_array($this->sortField, $this->validSortFields)) {
			$this->sortField = $this->defaultSortField;
		}
	}
	
	/**
	 * Validates the given sort order parameter. 
	 */
	public function validateSortOrder() {
		// call validateSortOrder event
		EventHandler::getInstance()->fireAction($this, 'validateSortOrder');
		
		switch ($this->sortOrder) {
			case 'ASC':
			case 'DESC':
			break;
			
			default:
				$this->sortOrder = $this->defaultSortOrder;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		// assign sorting parameters
		WCF::getTPL()->assign([
			'sortField' => $this->sortField,
			'sortOrder' => $this->sortOrder
		]);
	}
}
