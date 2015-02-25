<?php
namespace wcf\acp\form;
use wcf\data\bbcode\attribute\BBCodeAttribute;
use wcf\data\bbcode\attribute\BBCodeAttributeAction;
use wcf\data\bbcode\BBCode;
use wcf\data\bbcode\BBCodeAction;
use wcf\form\AbstractForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\language\I18nHandler;
use wcf\system\WCF;

/**
 * Shows the bbcode edit form.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
class BBCodeEditForm extends BBCodeAddForm {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.bbcode';
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.content.bbcode.canManageBBCode');
	
	/**
	 * bbcode id
	 * @var	integer
	 */
	public $bbcodeID = 0;
	
	/**
	 * bbcode object
	 * @var	\wcf\data\bbcode\BBCode
	 */
	public $bbcode = null;
	
	/**
	 * list of native bbcodes
	 * @var	array<string>
	 */
	public static $nativeBBCodes = array('b', 'i', 'u', 's', 'sub', 'sup', 'list', 'align', 'color', 'size', 'font', 'url', 'img', 'email', 'table');
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		AbstractForm::readParameters();
		
		if (isset($_REQUEST['id'])) $this->bbcodeID = intval($_REQUEST['id']);
		$this->bbcode = new BBCode($this->bbcodeID);
		if (!$this->bbcode->bbcodeID) {
			throw new IllegalLinkException();
		}
		
		if (!in_array($this->bbcode->bbcodeTag, self::$nativeBBCodes)) {
			I18nHandler::getInstance()->register('buttonLabel');
		}
	}
	
	/**
	 * @see	\wcf\acp\form\BBCodeAddForm::readButtonLabelFormParameter()
	 */
	protected function readButtonLabelFormParameter() {
		if (!in_array($this->bbcode->bbcodeTag, self::$nativeBBCodes)) {
			parent::readButtonLabelFormParameter();
		}
	}
	
	/**
	 * @see	\wcf\form\IForm::save()
	 */
	public function save() {
		AbstractForm::save();
		
		if ($this->showButton) {
			$this->buttonLabel = 'wcf.bbcode.buttonLabel'.$this->bbcode->bbcodeID;
			if (I18nHandler::getInstance()->isPlainValue('buttonLabel')) {
				I18nHandler::getInstance()->remove($this->buttonLabel);
				$this->buttonLabel = I18nHandler::getInstance()->getValue('buttonLabel');
			}
			else {
				I18nHandler::getInstance()->save('buttonLabel', $this->buttonLabel, 'wcf.bbcode', 1);
			}
		}
		
		// update bbcode
		$this->objectAction = new BBCodeAction(array($this->bbcodeID), 'update', array('data' => array_merge($this->additionalFields, array(
			'allowedChildren' => $this->allowedChildren,
			'bbcodeTag' => $this->bbcodeTag,
			'buttonLabel' => $this->buttonLabel,
			'className' => $this->className,
			'htmlClose' => $this->htmlClose,
			'htmlOpen' => $this->htmlOpen,
			'isSourceCode' => ($this->isSourceCode ? 1 : 0),
			'showButton' => ($this->showButton ? 1 : 0),
			'wysiwygIcon' => $this->wysiwygIcon
		))));
		$this->objectAction->executeAction();
		
		// clear existing attributes
		$sql = "DELETE FROM	wcf".WCF_N."_bbcode_attribute
			WHERE		bbcodeID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($this->bbcodeID));
		
		foreach ($this->attributes as $attribute) {
			$attributeAction = new BBCodeAttributeAction(array(), 'create', array('data' => array(
				'bbcodeID' => $this->bbcodeID,
				'attributeNo' => $attribute->attributeNo,
				'attributeHtml' => $attribute->attributeHtml,
				'validationPattern' => $attribute->validationPattern,
				'required' => $attribute->required,
				'useText' => $attribute->useText,
			)));
			$attributeAction->executeAction();
		}
		
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
		
		if (empty($_POST)) {
			I18nHandler::getInstance()->setOptions('buttonLabel', 1, $this->bbcode->buttonLabel, 'wcf.bbcode.buttonLabel\d+');
			$this->buttonLabel = $this->bbcode->buttonLabel;
			
			$this->attributes = BBCodeAttribute::getAttributesByBBCode($this->bbcode);
			$this->bbcodeTag = $this->bbcode->bbcodeTag;
			$this->htmlOpen = $this->bbcode->htmlOpen;
			$this->htmlClose = $this->bbcode->htmlClose;
			$this->allowedChildren = $this->bbcode->allowedChildren;
			$this->isSourceCode = $this->bbcode->isSourceCode;
			$this->className = $this->bbcode->className;
			$this->showButton = $this->bbcode->showButton;
			$this->wysiwygIcon = $this->bbcode->wysiwygIcon;
		}
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		I18nHandler::getInstance()->assignVariables(!empty($_POST));
		
		WCF::getTPL()->assign(array(
			'bbcode' => $this->bbcode,
			'action' => 'edit',
			'nativeBBCode' => (in_array($this->bbcode->bbcodeTag, self::$nativeBBCodes))
		));
	}
}
