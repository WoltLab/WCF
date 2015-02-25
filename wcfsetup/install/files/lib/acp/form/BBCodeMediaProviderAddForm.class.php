<?php
namespace wcf\acp\form;
use wcf\data\bbcode\media\provider\BBCodeMediaProviderAction;
use wcf\form\AbstractForm;
use wcf\system\exception\UserInputException;
use wcf\system\Regex;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows the BBCode media provider add form.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
class BBCodeMediaProviderAddForm extends AbstractForm {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.bbcode.mediaProvider.add';
	
	/**
	 * html value
	 * @var	string
	 */
	public $html = '';
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.content.bbcode.canManageBBCode');
	
	/**
	 * @see	\wcf\page\AbstractPage::$templateName
	 */
	public $templateName = 'bbcodeMediaProviderAdd';
	
	/**
	 * title value
	 * @var	string
	 */
	public $title = '';
	
	/**
	 * regex value
	 * @var	string
	 */
	public $regex = '';
	
	/**
	 * @see	\wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['title'])) $this->title = StringUtil::trim($_POST['title']);
		if (isset($_POST['regex'])) $this->regex = StringUtil::trim($_POST['regex']);
		if (isset($_POST['html'])) $this->html = StringUtil::trim($_POST['html']);
	}
	
	/**
	 * @see	\wcf\form\IForm::validate()
	 */
	public function validate() {
		parent::validate();
		
		// validate fields
		if (empty($this->title)) {
			throw new UserInputException('title');
		}
		if (empty($this->regex)) {
			throw new UserInputException('regex');
		}
		if (empty($this->html)) {
			throw new UserInputException('html');
		}
		
		$lines = explode("\n", StringUtil::unifyNewlines($this->regex));
		
		foreach ($lines as $line) {
			if (!Regex::compile($line)->isValid()) throw new UserInputException('regex', 'notValid');
		}
	}
	
	/**
	 * @see	\wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		// save media provider
		$this->objectAction = new BBCodeMediaProviderAction(array(), 'create', array('data' => array_merge($this->additionalFields, array(
			'title' => $this->title,
			'regex' => $this->regex,
			'html' => $this->html
		))));
		$this->objectAction->executeAction();
		$this->saved();
		
		// reset values
		$this->title = $this->regex = $this->html = '';
		
		// show success
		WCF::getTPL()->assign(array(
			'success' => true
		));
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'action' => 'add',
			'title' => $this->title,
			'regex' => $this->regex,
			'html' => $this->html
		));
	}
}
