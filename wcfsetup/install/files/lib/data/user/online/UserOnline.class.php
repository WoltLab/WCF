<?php
namespace wcf\data\user\online;
use wcf\data\user\UserProfile;
use wcf\system\cache\builder\SpiderCacheBuilder;
use wcf\system\WCF;
use wcf\util\StringUtil;
use wcf\util\UserUtil;

/**
 * Represents a user who is online.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.user
 * @subpackage	data.user.online
 * @category	Community Framework
 */
class UserOnline extends UserProfile {
	/**
	 * location of the user
	 * @var	string
	 */
	protected $location = '';
	
	/**
	 * spider object
	 * @var wcf\data\spider\Spider
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
			$username = sprintf($this->userOnlineMarking, $username);
		}
		
		if ($this->canViewOnlineStatus == 3) {
			$username .= WCF::getLanguage()->get('wcf.user.usersOnline.invisible');
		}
		
		return $username;
	}
	
	/**
	 * Sets the location of the user.
	 * 
	 * @param	string		$location
	 */
	public function setLocation($location) {
		$this->location = $location;
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
		// opera
		if (preg_match('~opera.*version/([\d\.]+)~i', $this->userAgent, $match)) {
			return 'Opera '.$match[1];
		}
		
		// firefox
		if (preg_match('~firefox/([\d\.]+)~i', $this->userAgent, $match)) {
			return 'Firefox '.$match[1];
		}
		
		// ie
		if (preg_match('~msie ([\d\.]+)~i', $this->userAgent, $match)) {
			return 'Internet Explorer '.$match[1];
		}
		
		// chrome
		if (preg_match('~chrome/([\d\.]+)~i', $this->userAgent, $match)) {
			return 'Chrome '.$match[1];
		}
		
		// safari
		if (preg_match('~([\d\.]+) safari~i', $this->userAgent, $match)) {
			return 'Safari '.$match[1];
		}
		
		return $this->userAgent;
	}
	
	/**
	 * Returns the spider object
	 * 
	 * @return	wcf\data\spider\Spider
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
