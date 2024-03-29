<?php

namespace wcf\system\message\embedded\object;

use wcf\data\user\UserList;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\util\StringUtil;

/**
 * IMessageEmbeddedObjectHandler implementation for quotes.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class QuoteMessageEmbeddedObjectHandler extends AbstractMessageEmbeddedObjectHandler
{
    /**
     * @inheritDoc
     */
    public function parse(HtmlInputProcessor $htmlInputProcessor, array $embeddedData)
    {
        $usernames = [];

        $quoteElements = $htmlInputProcessor->getHtmlInputNodeProcessor()
            ->getDocument()
            ->getElementsByTagName('woltlab-quote');
        /** @var \DOMElement $element */
        foreach ($quoteElements as $element) {
            $username = StringUtil::trim($element->getAttribute('data-author'));
            if (!empty($username) && !\in_array($username, $usernames)) {
                $usernames[] = $username;
            }
        }

        if (!empty($usernames)) {
            $userList = new UserList();
            $userList->getConditionBuilder()->add("user_table.username IN (?)", [$usernames]);
            $userList->readObjectIDs();

            return $userList->getObjectIDs();
        }

        return [];
    }

    /**
     * @inheritDoc
     */
    public function loadObjects(array $objectIDs)
    {
        return UserProfileRuntimeCache::getInstance()->getObjects($objectIDs);
    }
}
