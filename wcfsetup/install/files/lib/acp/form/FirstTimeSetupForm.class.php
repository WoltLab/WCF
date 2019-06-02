<?php
namespace wcf\acp\form;
use wcf\data\option\OptionAction;
use wcf\system\option\OptionHandler;
use wcf\system\WCF;

/**
 * Shows the option edit form.
 * 
 * @author      Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\Acp\Form
 * 
 * @property OptionHandler $optionHandler
 */
class FirstTimeSetupForm extends AbstractOptionListForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.configuration';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.configuration.canEditOption'];
	
	/**
	 * list of options
	 * @var array
	 */
	public $options = [];
	
	/**
	 * @var string[]
	 */
	public $optionNames = [
		'page_title',
		'timezone',
		'mail_from_name',
		'mail_from_address',
		'mail_admin_address',
		'image_allow_external_source',
		'module_contact_form',
		'log_ip_address',
		'package_server_auth_code',
		'recaptcha_publickey',
		'recaptcha_privatekey'
	];
	
	/**
	 * @inheritDoc
	 */
	protected function initOptionHandler() {
		parent::initOptionHandler();
		
		$this->optionHandler->filterOptions($this->optionNames);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		foreach ($this->optionNames as $optionName) {
			$this->options[] = $this->optionHandler->getSingleOption($optionName);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		$saveOptions = $this->optionHandler->save('wcf.acp.option', 'wcf.acp.option.option');
		$this->objectAction = new OptionAction([], 'updateAll', ['data' => $saveOptions]);
		$this->objectAction->executeAction();
		$this->saved();
		
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'options' => $this->options,
			'optionNames' => $this->optionNames,
		]);
	}
}
