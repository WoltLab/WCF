<?php
namespace wcf\data;
use wcf\system\feed\enclosure\FeedEnclosure;

/**
 * Every feed entry that supports enclosure tags should implement this interface.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data
 */
interface IFeedEntryWithEnclosure extends IFeedEntry {
	/**
	 * Returns the enclosure object
	 * 
	 * @return FeedEnclosure|null
	 */
	public function getEnclosure();
}
