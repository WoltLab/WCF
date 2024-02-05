<?php

namespace wcf\data;

/**
 * Every feed entry should implement this interface.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated 6.1 use `wcf\system\rssFeed\RssFeedItem` instead
 */
interface IFeedEntry extends IMessage
{
    /**
     * Returns the number of comments.
     *
     * @return  int
     */
    public function getComments();

    /**
     * Returns a list of category names.
     *
     * @return  string[]
     */
    public function getCategories();
}
