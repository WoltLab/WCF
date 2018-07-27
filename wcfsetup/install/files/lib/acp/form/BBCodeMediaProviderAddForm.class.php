<?php
namespace wcf\acp\form;
use wcf\data\bbcode\media\provider\BBCodeMediaProvider;
use wcf\data\bbcode\media\provider\BBCodeMediaProviderAction;
use wcf\data\bbcode\media\provider\BBCodeMediaProviderEditor;
use wcf\form\AbstractForm;
use wcf\system\exception\UserInputException;
use wcf\system\Regex;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows the BBCode media provider add form.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 */
class BBCodeMediaProviderAddForm extends AbstractForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.bbcode.mediaProvider.add';
	
	/**
	 * media provider class name
	 * @var	string
	 */
	public $className = '';
	
	/**
	 * media provider package id
	 * @var	integer
	 */
	public $packageID = PACKAGE_ID;
	
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
		if (isset($_POST['className'])) $this->className = StringUtil::trim($_POST['className']);
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
		if (empty($this->className) && empty($this->html)) {
			throw new UserInputException('html');
		}
		// validate class name
		if (!empty($this->className) && !class_exists($this->className)) {
			throw new UserInputException('className', 'notFound');
		}
		
		$lines = explode("\n", StringUtil::unifyNewlines($this->regex));
		
		foreach ($lines as $line) {
			if (!Regex::compile($line)->isValid()) throw new UserInputException('regex', 'invalid');
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		$name = 'placeholder_'.StringUtil::getRandomID();
		
		// save media provider
		$this->objectAction = new BBCodeMediaProviderAction([], 'create', ['data' => array_merge($this->additionalFields, [
			'title' => $this->title,
			'regex' => $this->regex,
			'html' => $this->html,
			'className' => $this->className,
			'packageID' => $this->packageID,
			'name' => $name
		])]);
		$returnValues = $this->objectAction->executeAction();
		$this->saved();
		
		/** @var BBCodeMediaProvider $provider */
		$provider = $returnValues['returnValues'];
		(new BBCodeMediaProviderEditor($provider))->update([
			'name' => 'com.woltlab.wcf.generic' . $provider->providerID
		]);
		
		// reset values
		$this->title = $this->regex = $this->html = $this->className = '';
		
		// show success message
		WCF::getTPL()->assign('success', true);
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
			'html' => $this->html,
			'className' => $this->className
		]);
	}
}
