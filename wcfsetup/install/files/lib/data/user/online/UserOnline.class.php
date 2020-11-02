<?php
namespace wcf\data\user\online;
use wcf\data\page\PageCache;
use wcf\data\spider\Spider;
use wcf\data\user\UserProfile;
use wcf\system\cache\builder\SpiderCacheBuilder;
use wcf\system\event\EventHandler;
use wcf\system\page\handler\IOnlineLocationPageHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;
use wcf\util\UserAgent;
use wcf\util\UserUtil;

/**
 * Represents a user who is online.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\User\Online
 *
 * @property-read	integer|null	$pageID			id of the last visited page
 * @property-read	integer|null	$pageObjectID		id of the object the last visited page belongs to
 * @property-read	integer|null	$parentPageObjectID	id of the parent of the object the last visited page belongs to
 * @property-read	string|null	$userOnlineMarking	HTML code used to print the formatted name of a user group member
 */
class UserOnline extends UserProfile {
	/**
	 * location of the user
	 * @var	string
	 */
	protected $location = '';
	
	/**
	 * spider object
	 * @var	Spider
	 */
	protected $spider = null;
	
	/**
	 * Returns the formatted username.
	 * 
	 * @return	string
	 */
	public function getFormattedUsername() {
		$username = StringUtil::encodeHTML($this->username);
		
		if ($this->userOnlineMarking && $this->userOnlineMarking != '%s') {
			$username = str_replace('%s', $username, $this->userOnlineMarking);
		}
		
		if (
			$this->getPermission('user.profile.canHideOnlineStatus')
			&& $this->canViewOnlineStatus == UserProfile::ACCESS_NOBODY
		) {
			$username .= WCF::getLanguage()->get('wcf.user.usersOnline.invisible');
		}
		
		return $username;
	}
	
	/**
	 * Sets the location of the user. If no location is given, the method tries to
	 * automatically determine the location.
	 * 
	 * @param	string|null	$location
	 * @return	boolean		`true` if the location has been successfully set, otherwise `false`
	 */
	public function setLocation($location = null) {
		if ($location === null) {
			if ($this->pageID) {
				$page = PageCache::getInstance()->getPage($this->pageID);
				if ($page !== null) {
					if ($page->getHandler() !== null && $page->getHandler() instanceof IOnlineLocationPageHandler) {
						// refer to page handler
						/** @noinspection PhpUndefinedMethodInspection */
						$this->location = $page->getHandler()->getOnlineLocation($page, $this);
						return true;
					}
					else if ($page->isVisible() && $page->isAccessible()) {
						$title = $page->getTitle();
						if (!empty($title)) {
							if ($page->pageType != 'system') {
								$this->location = '<a href="' . StringUtil::encodeHTML($page->getLink()) . '">' . StringUtil::encodeHTML($title) . '</a>';
							}
							else {
								$this->location = StringUtil::encodeHTML($title);
							}
						}
						
						return ($this->location != '');
					}
				}
			}
			
			$this->location = '';
			return false;
		}
		
		$this->location = $location;
		return true;
	}
	
	/**
	 * Returns the location of the user.
	 * 
	 * @return	string
	 */
	public function getLocation() {
		return $this->location;
	}
	
	/**
	 * Returns the ip address.
	 * 
	 * @return	string
	 */
	public function getFormattedIPAddress() {
		if ($address = UserUtil::convertIPv6To4($this->ipAddress)) {
			return $address;
		}
		
		return $this->ipAddress;
	}
	
	/**
	 * Tries to retrieve browser name and version.
	 * 
	 * @return	string
	 */
	public function getBrowser() {
		$parameters = ['browser' => '', 'userAgent' => $this->userAgent];
		EventHandler::getInstance()->fireAction($this, 'getBrowser', $parameters);
		if (!empty($parameters['browser'])) {
			return $parameters['browser'];
		}
		
		$userAgent = new UserAgent($this->userAgent);
		if ($userAgent->getBrowser() === null) {
			return $this->userAgent;
		}
		
		$browserVersion = $userAgent->getBrowserVersion();
		return $userAgent->getBrowser() . ($browserVersion ? ' ' . $browserVersion : '');
	}
	
	/**
	 * Returns the spider object
	 * 
	 * @return	Spider
	 */
	public function getSpider() {
		if (!$this->spiderID) return null;
		
		if ($this->spider === null) {
			$spiderList = SpiderCacheBuilder::getInstance()->getData();
			$this->spider = $spiderList[$this->spiderID];
		}
		
		return $this->spider;
	}
}
