<?php
namespace wcf\data\option;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\email\transport\SmtpEmailTransport;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * Executes option-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Option
 *  
 * @method	Option		create()
 * @method	OptionEditor[]	getObjects()
 * @method	OptionEditor	getSingleObject()
 */
class OptionAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $className = OptionEditor::class;
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsCreate = ['admin.configuration.canEditOption'];
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsDelete = ['admin.configuration.canEditOption'];
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsUpdate = ['admin.configuration.canEditOption'];
	
	/**
	 * @inheritDoc
	 */
	protected $requireACP = ['create', 'delete', 'emailSmtpTest', 'import', 'update', 'updateAll'];
	
	/**
	 * Validates permissions and parameters.
	 */
	public function validateImport() {
		parent::validateCreate();
	}
	
	/**
	 * Validates permissions and parameters.
	 */
	public function validateUpdateAll() {
		parent::validateCreate();
	}
	
	/**
	 * Imports options.
	 */
	public function import() {
		// create data
		call_user_func([$this->className, 'import'], $this->parameters['data']);
	}
	
	/**
	 * Updates the value of all given options.
	 */
	public function updateAll() {
		// create data
		call_user_func([$this->className, 'updateAll'], $this->parameters['data']);
	}
	
	/**
	 * Validates the basic SMTP connection parameters.
	 * 
	 * @throws      UserInputException
	 */
	public function validateEmailSmtpTest() {
		WCF::getSession()->checkPermissions($this->permissionsUpdate);
		
		$this->readString('host');
		$this->readInteger('port');
		$this->readString('startTls');
		
		$this->readString('user', true);
		$this->readString('password', true);
		if (!empty($this->parameters['user']) && empty($this->parameters['password'])) {
			throw new UserInputException('password');
		}
		else if (empty($this->parameters['user']) && !empty($this->parameters['password'])) {
			throw new UserInputException('user');
		}
	}
	
	/**
	 * Runs a simple test of the SMTP connection.
	 * 
	 * @return      string[]
	 */
	public function emailSmtpTest() {
		$smtp = new SmtpEmailTransport(
			$this->parameters['host'],
			$this->parameters['port'],
			$this->parameters['user'],
			$this->parameters['password'],
			$this->parameters['startTls']
		);
		
		return ['validationResult' => $smtp->testConnection()];
	}
}
