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
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 */
class TemplateEditForm extends TemplateAddForm {
	/**
	 * @inheritDoc
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
	 * @inheritDoc
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
	 * @inheritDoc
	 */
	protected function validateName() {
		if ($this->tplName != $this->template->templateName) {
			parent::validateName();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function validateGroup() {
		if ($this->templateGroupID != $this->template->templateGroupID) {
			parent::validateName();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		AbstractForm::save();
		
		$this->objectAction = new TemplateAction([$this->template], 'update', ['data' => array_merge($this->additionalFields, [
			'templateName' => $this->tplName,
			'templateGroupID' => $this->templateGroupID,
			'lastModificationTime' => TIME_NOW
		]), 'source' => $this->templateSource]);
		$this->objectAction->executeAction();
		$this->saved();
		
		// show success
		WCF::getTPL()->assign([
			'success' => true
		]);
	}
	
	/**
	 * @inheritDoc
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
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'action' => 'edit',
			'templateID' => $this->templateID,
			'template' => $this->template
		]);
	}
}
