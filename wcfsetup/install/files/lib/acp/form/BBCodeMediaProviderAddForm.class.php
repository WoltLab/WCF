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
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 */
class BBCodeMediaProviderAddForm extends AbstractForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.bbcode.mediaProvider.add';
	
	/**
	 * html value
	 * @var	string
	 */
	public $html = '';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.content.bbcode.canManageBBCode'];
	
	/**
	 * @inheritDoc
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
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['title'])) $this->title = StringUtil::trim($_POST['title']);
		if (isset($_POST['regex'])) $this->regex = StringUtil::trim($_POST['regex']);
		if (isset($_POST['html'])) $this->html = StringUtil::trim($_POST['html']);
	}
	
	/**
	 * @inheritDoc
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
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		// save media provider
		$this->objectAction = new BBCodeMediaProviderAction([], 'create', ['data' => array_merge($this->additionalFields, [
			'title' => $this->title,
			'regex' => $this->regex,
			'html' => $this->html
		])]);
		$this->objectAction->executeAction();
		$this->saved();
		
		// reset values
		$this->title = $this->regex = $this->html = '';
		
		// show success
		WCF::getTPL()->assign([
			'success' => true
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'action' => 'add',
			'title' => $this->title,
			'regex' => $this->regex,
			'html' => $this->html
		]);
	}
}
