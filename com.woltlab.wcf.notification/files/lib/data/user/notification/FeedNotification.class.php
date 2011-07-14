<?php
// wcf imports
require_once(WCF_DIR.'lib/data/user/notification/Notification.class.php');

/**
 * Represents a notification in a rss or atom feed
 *
 * @author	Oliver Kliebisch
 * @copyright	2009-2010 Oliver Kliebisch
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.community.wcf.user.notification
 * @subpackage	data.user.notification
 * @category 	Community Framework
 */
class FeedNotification extends Notification {
	/**
	 * @see Notification::handleData()         
	 */
	protected function handleData($data) {
		// escape CDATA
		$data['shortOutput'] = StringUtil::escapeCDATA(StringUtil::stripHTML($data['shortOutput']));
		$data['mediumOutput'] = StringUtil::escapeCDATA($data['mediumOutput']);
		$data['longOutput'] = StringUtil::escapeCDATA($data['longOutput']);
		if (isset($data['messageCache'])) $data['messageCache'] = StringUtil::escapeCDATA($data['messageCache']);

		parent::handleData($data);
	}

	/**
	 * @see Notification::parseURLsCallback()
	 */
	protected function parseURLsCallback($match) {
		$url = parent::parseURLsCallback($match);

		$url = FileUtil::addTrailingSlash(PAGE_URL).$url;

		return $url;
	}
}
?>