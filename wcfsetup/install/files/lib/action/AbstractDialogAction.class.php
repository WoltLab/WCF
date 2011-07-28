<?php
namespace wcf\action;
use wcf\system\exception\AJAXException;
use wcf\util\JSON;
use wcf\util\StringUtil;

abstract class AbstractDialogAction extends AbstractSecureAction {
	/**
	 * current step
	 *
	 * @var	string
	 */
	public $step = '';
	
	/**
	 * template name
	 *
	 * @var	string
	 */
	public $templateName = '';
	
	/**
	 * response data
	 *
	 * @var	array
	 */
	public $data = array();
	
	/**
	 * @see	wcf\action\IAction::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (empty($this->templateName)) {
			throw new IllegalLinkException();
		}
		
		if (isset($_REQUEST['step'])) {
			$this->step = StringUtil::trim($_REQUEST['step']);
			
			// append step as part of template name
			$this->templateName .= StringUtil::firstCharToUpperCase($this->step);
		}
		
		$this->validateStep();
	}
	
	public final function execute() {
		parent::execute();
		
		$methodName = 'step' . StringUtil::firstCharToUpperCase($this->step);
		if (!method_exists($this, $methodName)) {
			throw new AJAXException("Class '".get_class($this)."' does not implement the required method '".$methodName."'");
		}
		
		// execute step
		$this->{$methodName}();
		
		$this->executed();
		
		// send JSON-encoded response
		header('Content-type: application/json');
		echo JSON::encode($this->data);
		exit;
	}
	
	/**
	 * Validates current dialog step.
	 */
	abstract protected function validateStep();
}
