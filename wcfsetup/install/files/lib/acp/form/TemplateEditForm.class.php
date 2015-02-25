<?php
namespace wcf\acp\form;
use wcf\data\template\Template;
use wcf\data\template\TemplateAction;
use wcf\form\AbstractForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

/**
 * Shows the form for adding new templates.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
class TemplateEditForm extends TemplateAddForm {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.template';
	
	/**
	 * template id
	 * @var	integer
	 */
	public $templateID = 0;
	
	/**
	 * template object
	 * @var	\wcf\data\template\Template
	 */
	public $template = null;
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->templateID = intval($_REQUEST['id']);
		$this->template = new Template($this->templateID);
		if (!$this->template->templateID || !$this->template->templateGroupID) {
			throw new IllegalLinkException();
		}
		$this->packageID = $this->template->packageID;
	}
	
	/**
	 * @see	\wcf\acp\form\TemplateAddForm::validateName()
	 */
	protected function validateName() {
		if ($this->tplName != $this->template->templateName) {
			parent::validateName();
		}
	}
	
	/**
	 * @see	\wcf\acp\form\TemplateAddForm::validateName()
	 */
	protected function validateGroup() {
		if ($this->templateGroupID != $this->template->templateGroupID) {
			parent::validateName();
		}
	}
	
	/**
	 * @see	\wcf\form\IForm::save()
	 */
	public function save() {
		AbstractForm::save();
		
		$this->objectAction = new TemplateAction(array($this->template), 'update', array('data' => array_merge($this->additionalFields, array(
			'templateName' => $this->tplName,
			'templateGroupID' => $this->templateGroupID,
			'lastModificationTime' => TIME_NOW
		)), 'source' => $this->templateSource));
		$this->objectAction->executeAction();
		$this->saved();
		
		// show success
		WCF::getTPL()->assign(array(
			'success' => true
		));
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		if (!count($_POST)) {
			$this->tplName = $this->template->templateName;
			$this->templateSource = $this->template->getSource();
			$this->templateGroupID = $this->template->templateGroupID;
		}
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'action' => 'edit',
			'templateID' => $this->templateID,
			'template' => $this->template
		));
	}
}
