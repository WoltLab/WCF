<?php
namespace wcf\page;
use wcf\system\WCF;

class SortableAJAXPage extends SortablePage {
	/**
	 * Contains the name of the template that is used to render each specific item. 
	 */
	protected $listContentTemplate;
	
	/**
	 * Contains wether the current request is done via AJAX or not.
	 */
	protected $ajax = false;
	
	/**
	 * @see wcf\data\page\IPage::readParameters() 
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['ajax'])) $this->ajax = true;
	}
	
	/**
	 * @see wcf\data\page\IPage::show() 
	 */
	public function show() {
		if ($this->ajax) {
			$this->templateName = $this->listContentTemplate;
		}
		
		parent::show();
	}
	
}