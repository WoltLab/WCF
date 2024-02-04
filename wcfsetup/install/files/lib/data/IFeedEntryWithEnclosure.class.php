<?php

namespace wcf\data;

use wcf\system\feed\enclosure\FeedEnclosure;

/**
 * Every feed entry that supports enclosure tags should implement this interface.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated 6.1 use `wcf\system\rssFeed\RssFeedItem` instead
 */
interface IFeedEntryWithEnclosure extends IFeedEntry
{
    /**
     * Returns the enclosure object
     *
     * @return FeedEnclosure|null
     */
    public function getEnclosure();
}
