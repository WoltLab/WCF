<?php
namespace wcf\data\user\trophy;
use wcf\data\trophy\Trophy;
use wcf\data\trophy\TrophyCache;
use wcf\data\user\User;
use wcf\data\user\UserProfile;
use wcf\data\DatabaseObject;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\event\EventHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Represents a user trophy.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\User\Trophy
 * @since	3.1
 *
 * @property-read	integer		$userTrophyID			unique id of the user trophy
 * @property-read	integer		$trophyID			trophy id
 * @property-read	integer		$userID				user id
 * @property-read	integer		$time				the time when the trophy was rewarded
 * @property-read	string		$description			the custom trophy description
 * @property-read	string		$useCustomDescription		`1`, if the trophy use a custom description
 * @property-read	integer		$trophyUseHtml		        `1`, if the trophy use a html description
 */
class UserTrophy extends DatabaseObject {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'userTrophyID';
	
	/**
	 * The description text replacements. 
	 * @var string[]
	 */
	private $replacements; 
	
	/**
	 * Returns the trophy for the user trophy. 
	 * 
	 * @return Trophy
	 */
	public function getTrophy() {
		return TrophyCache::getInstance()->getTrophyByID($this->trophyID);
	}
	
	/**
	 * Returns the user profile for the user trophy.
	 *
	 * @return UserProfile
	 */
	public function getUserProfile() {
		return UserProfileRuntimeCache::getInstance()->getObject($this->userID);
	}
	
	/**
	 * Returns the parsed description.
	 * 
	 * @return string
	 */
	public function getDescription() {
		if (!$this->useCustomDescription) {
			return $this->getTrophy()->getDescription();
		}
		
		if (!$this->trophyUseHtml) {
			return nl2br(StringUtil::encodeHTML(strtr(WCF::getLanguage()->get($this->description), $this->getReplacements())), false);
		}
		
		return strtr(WCF::getLanguage()->get($this->description), $this->getReplacements());
	}
	
	/**
	 * Returns true, if the given user can see this user trophy.
	 *
	 * @param 	User 	$user
	 * @return 	bool
	 */
	public function canSee(User $user = null) {
		if ($user === null) {
			$user = WCF::getUser();
		}
		
		if (!$user->userID) {
			$userProfile = new UserProfile(new User(null, []));
		} 
		else {
			$userProfile = UserProfileRuntimeCache::getInstance()->getObject($user->userID);
		}
		
		if (!$userProfile->getPermission('user.profile.trophy.canSeeTrophies')) {
			return false;
		}
		
		if ($this->getTrophy()->isDisabled()) {
			return false;
		}
		
		return $this->getUserProfile()->isAccessible('canViewTrophies') || $user->userID == $this->userID;
	}
	
	/**
	 * Returns an array with replacements for the trophy. 
	 * 
	 * @return string[]
	 */
	protected function getReplacements() {
		if ($this->replacements == null) {
			$replacements = [
				'{$username}' => $this->getUserProfile()->username
			];
			
			$parameters = ['replacements' => $replacements];
			
			EventHandler::getInstance()->fireAction($this, 'getReplacements', $parameters);
			
			$this->replacements = $parameters['replacements'];
		}
		
		return $this->replacements; 
	}
}
