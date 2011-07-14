<?php
namespace wcf\system\request;
use wcf\system\application\ApplicationHandler;
use wcf\system\SingletonFactory;

/**
 * Handles relative links within the wcf.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.request
 * @category 	Community Framework
 */
class LinkHandler extends SingletonFactory {
	/**
	 * Returns a relative link.
	 * 
	 * @param 	string		$url
	 * @param 	string		$abbreviation
	 * @return	string
	 */
	public function getLink($url, $abbreviation = 'wcf') {
		$applicationGroup = ApplicationHandler::getInstance()->getActiveGroup();
		
		// not within an application group, return unmodified url
		if ($applicationGroup === null) {
			return $url . (strstr($url, '?') === false ? SID_ARG_1ST : SID_ARG_2ND_NOT_ENCODED);
		}
		
		// try to resolve abbreviation
		$application = null;
		if ($abbreviation != 'wcf') {
			$application = ApplicationHandler::getInstance()->getApplication($abbreviation);
		}
		
		// fallback to primary application if abbreviation is 'wcf' or unknown
		if ($application === null) {
			$application = ApplicationHandler::getInstance()->getPrimaryApplication();
		}
		
		return $application->domainName . $application->domainPath . $url . (strstr($url, '?') === false ? SID_ARG_1ST : SID_ARG_2ND_NOT_ENCODED);
	}
}
