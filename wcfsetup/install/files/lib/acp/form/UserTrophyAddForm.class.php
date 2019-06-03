<?php
namespace wcf\acp\form;
use wcf\data\trophy\category\TrophyCategoryCache;
use wcf\data\trophy\Trophy;
use wcf\data\trophy\TrophyCache;
use wcf\data\user\trophy\UserTrophyAction;
use wcf\data\user\trophy\UserTrophyEditor;
use wcf\data\user\UserProfile;
use wcf\system\exception\UserInputException;
use wcf\system\language\I18nHandler;
use wcf\system\language\I18nValue;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\StringUtil;

/**
 * User trophy add form.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 * @since	3.1
 */
class UserTrophyAddForm extends AbstractAcpForm {
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.trophy.canAwardTrophy'];
	
	/**
	 * @inheritDoc
	 */
	public $neededModules = ['MODULE_TROPHY'];
	
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.userTrophy.add';
	
	/**
	 * usernames (comma separated)
	 * @var string[]
	 */
	public $user = '';
	
	/**
	 * List of user ids which earn the trophy. 
	 * @var integer[]
	 */
	public $userIDs = [];
	
	/**
	 * `1` if the user trophy should have a custom description
	 * @var int
	 */
	public $useCustomDescription = 0;
	
	/**
	 * custom trophy description 
	 * @var string
	 */
	public $description;
	
	/**
	 * @var integer
	 */
	public $trophyID = 0;
	
	/**
	 * Rewarded trophy instance. 
	 * @var Trophy
	 */
	public $trophy = null;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		$descriptionI18n = new I18nValue('description');
		$descriptionI18n->setLanguageItem('wcf.user.trophy.userTrophy.description', 'wcf.user.trophy', 'com.woltlab.wcf');
		$descriptionI18n->setFlags(I18nValue::ALLOW_EMPTY);
		$this->registerI18nValue($descriptionI18n);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['user'])) $this->user = StringUtil::trim($_POST['user']);
		if (isset($_POST['trophyID'])) $this->trophyID = intval($_POST['trophyID']);
		if (isset($_POST['useCustomDescription'])) $this->useCustomDescription = 1;
		
		$this->trophy = new Trophy($this->trophyID);
	}
	
	/**
	 * Validates the users. 
	 * 
	 * @throws UserInputException
	 */
	protected function validateUser() {
		// read userIDs 
		$userAsArray = ArrayUtil::trim(explode(',', $this->user));
		
		$userList = UserProfile::getUserProfilesByUsername($userAsArray);
		
		$error = []; 
		
		foreach ($userList as $username => $user) {
			if ($user === null) {
				$error[] = [
					'type' => 'notFound', 
					'username' => $username
				];
			}
			else {
				$this->userIDs[] = $user->userID;
			}
		}
		
		if (!empty($error)) {
			throw new UserInputException('user', $error);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		parent::validate();
		
		if ($this->useCustomDescription) {
			if (!I18nHandler::getInstance()->validateValue('description')) {
				throw new UserInputException('description');
			}
		}
		
		$this->validateUser();
		
		if (empty($this->userIDs)) {
			throw new UserInputException('user');
		}
		
		if (!$this->trophy->trophyID) {
			throw new UserInputException('trophyID');
		}
		
		if ($this->trophy->awardAutomatically) {
			throw new UserInputException('trophyID', 'awardAutomatically');
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		foreach ($this->userIDs as $user) {
			$databaseObject = (new UserTrophyAction([], 'create', [
				'data' => array_merge($this->additionalFields, [
					'trophyID' => $this->trophy->trophyID,
					'userID' => $user,
					'description' => $this->useCustomDescription ? $this->description : '',
					'time' => TIME_NOW,
					'useCustomDescription' => $this->useCustomDescription
				])
			]))->executeAction();
			
			$this->saveI18n($databaseObject['returnValues'], UserTrophyEditor::class);
		}
		
		$this->reset();
	}
	
	/**
	 * @inheritDoc
	 */
	public function reset() {
		parent::reset();
		
		$this->user = '';
		$this->userIDs = [];
		$this->trophyID = '';
		$this->useCustomDescription = 0;
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'trophyID' => $this->trophyID,
			'user' => $this->user,
			'trophyCategories' => TrophyCategoryCache::getInstance()->getCategories(),
			'useCustomDescription' => $this->useCustomDescription, 
			'hasSuitableTrophy' => $this->hasSuitableTrophy()
		]);
	}
	
	/**
	 * Returns true if trophies exist that are not automatically awarded. 
	 * 
	 * @return bool
	 */
	private function hasSuitableTrophy() {
		foreach (TrophyCache::getInstance()->getTrophies() as $trophy) {
			if (!$trophy->awardAutomatically) {
				return true;
			}
		}
		
		return false;
	}
}
