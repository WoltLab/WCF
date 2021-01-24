<?php

namespace wcf\system\event\listener;

use wcf\page\UserPage;
use wcf\system\cache\runtime\UserRuntimeCache;
use wcf\system\html\input\node\HtmlInputNodeProcessor;
use wcf\system\request\LinkHandler;

/**
 * Automatically inserts the name of the user if their profile page is linked in messages.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Event\Listener
 * @since   5.4
 */
class UserLinkHtmlInputNodeProcessorListener extends AbstractHtmlInputNodeProcessorListener
{
    /**
     * @inheritDoc
     */
    public function execute($eventObj, $className, $eventName, array &$parameters)
    {
        /** @var HtmlInputNodeProcessor $eventObj */

        $regex = $this->getRegexFromLink(
            LinkHandler::getInstance()->getControllerLink(UserPage::class, [
                'forceFrontend' => true,
            ])
        );
        $userIDs = $this->getObjectIDs($eventObj, $regex);

        if (!empty($userIDs)) {
            $this->replaceLinks($eventObj, UserRuntimeCache::getInstance()->getObjects($userIDs));
        }
    }
}
