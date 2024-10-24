<?php

namespace wcf\util;

use wcf\data\user\group\UserGroup;
use wcf\system\application\ApplicationHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\Regex;
use wcf\system\WCF;

/**
 * Contains message-related functions.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class MessageUtil
{
    /**
     * Strips session links, html entities and \r\n from the given text.
     *
     * @param string $text
     * @return  string
     */
    public static function stripCrap($text)
    {
        // strip session links, security tokens and access tokens
        $text = Regex::compile('(?<=\?|&)([st]=[a-f0-9]{40}|at=\d+-[a-f0-9]{40})')->replace($text, '');

        // convert html entities (utf-8)
        $text = Regex::compile('&#(3[2-9]|[4-9][0-9]|\d{3,5});')->replace($text, static function ($matches) {
            return StringUtil::getCharacter(\intval($matches[1]));
        });

        // unify new lines
        $text = StringUtil::unifyNewlines($text);

        // remove control characters
        return \preg_replace('~[\x00-\x08\x0B-\x1F\x7F]~', '', $text);
    }

    /**
     * Returns the mentioned users in the given text.
     *
     * @param HtmlInputProcessor $htmlInputProcessor html input processor instance
     * @return      int[]                   ids of the mentioned users
     * @since       5.3
     */
    public static function getMentionedUserIDs(HtmlInputProcessor $htmlInputProcessor)
    {
        $userIDs = [];
        $groups = [];

        $elements = $htmlInputProcessor
            ->getHtmlInputNodeProcessor()
            ->getDocument()
            ->getElementsByTagName('woltlab-metacode');
        /** @var \DOMElement $element */
        foreach ($elements as $element) {
            $type = $element->getAttribute('data-name');
            if ($type !== 'user' && $type !== 'group') {
                continue;
            }

            if (DOMUtil::hasParent($element, 'woltlab-quote')) {
                // ignore mentions within quotes
                continue;
            }

            $attributes = $htmlInputProcessor->getHtmlInputNodeProcessor()->parseAttributes(
                $element->getAttribute('data-attributes')
            );

            if ($type === 'user') {
                if (!empty($attributes[0])) {
                    $userIDs[] = $attributes[0];
                }
            } elseif ($type === 'group' && WCF::getSession()->getPermission('user.message.canMentionGroups')) {
                if (!empty($attributes[0])) {
                    $group = UserGroup::getGroupByID($attributes[0]);
                    if ($group !== null && $group->canBeMentioned() && !isset($groups[$group->groupID])) {
                        $groups[$group->groupID] = $group;
                    }
                }
            }
        }

        $userIDs = \array_unique($userIDs);
        if (!empty($groups)) {
            $conditions = new PreparedStatementConditionBuilder();
            $conditions->add('groupID IN (?)', [\array_keys($groups)]);
            if (!empty($userIDs)) {
                $conditions->add('userID NOT IN (?)', [$userIDs]);
            }

            $sql = "SELECT  userID
                    FROM    wcf1_user_to_group
                    " . $conditions;
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute($conditions->getParameters());
            while ($userID = $statement->fetchColumn()) {
                $userIDs[] = $userID;
            }
        }

        return $userIDs;
    }

    /**
     * Returns the mentioned users in the given text.
     *
     * @param HtmlInputProcessor $htmlInputProcessor html input processor instance
     * @return      string[]                mentioned usernames
     * @deprecated  5.3
     */
    public static function getMentionedUsers(HtmlInputProcessor $htmlInputProcessor)
    {
        $usernames = [];
        $groups = [];

        $elements = $htmlInputProcessor
            ->getHtmlInputNodeProcessor()
            ->getDocument()
            ->getElementsByTagName('woltlab-metacode');
        /** @var \DOMElement $element */
        foreach ($elements as $element) {
            $type = $element->getAttribute('data-name');
            if ($type !== 'user' && $type !== 'group') {
                continue;
            }

            if (DOMUtil::hasParent($element, 'woltlab-quote')) {
                // ignore mentions within quotes
                continue;
            }

            if ($type === 'user') {
                $usernames[] = $element->textContent;
            } elseif ($type === 'group' && WCF::getSession()->getPermission('user.message.canMentionGroups')) {
                $attributes = $htmlInputProcessor->getHtmlInputNodeProcessor()->parseAttributes(
                    $element->getAttribute('data-attributes')
                );

                if (!empty($attributes[0])) {
                    $group = UserGroup::getGroupByID($attributes[0]);
                    if ($group !== null && $group->canBeMentioned() && !isset($groups[$group->groupID])) {
                        $groups[$group->groupID] = $group;
                    }
                }
            }
        }

        if (!empty($groups)) {
            $conditions = new PreparedStatementConditionBuilder();
            $conditions->add('user_to_group.groupID IN (?)', [\array_keys($groups)]);
            if (!empty($usernames)) {
                $conditions->add('user_table.username NOT IN (?)', [$usernames]);
            }

            $sql = "SELECT      user_table.username
                    FROM        wcf1_user_to_group user_to_group
                    LEFT JOIN   wcf1_user user_table
                    ON          user_table.userID = user_to_group.userID
                    " . $conditions;
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute($conditions->getParameters());
            while ($username = $statement->fetchColumn()) {
                $usernames[] = $username;
            }
        }

        return $usernames;
    }

    /**
     * Returns the quoted users in the given text.
     *
     * @param HtmlInputProcessor $htmlInputProcessor html input processor instance
     * @return      string[]                quoted usernames
     */
    public static function getQuotedUsers(HtmlInputProcessor $htmlInputProcessor)
    {
        $ownHost = ApplicationHandler::getInstance()->getDomainName();
        $usernames = [];

        $elements = $htmlInputProcessor
            ->getHtmlInputNodeProcessor()
            ->getDocument()
            ->getElementsByTagName('woltlab-quote');
        /** @var \DOMElement $element */
        foreach ($elements as $element) {
            $username = $element->getAttribute('data-author');
            if (!empty($username)) {
                // check if there is a link set and if it points to any of the apps
                $link = $element->getAttribute('data-link');
                $host = ($link) ? Url::parse($link)['host'] : '';
                if ($host !== $ownHost) {
                    // links mismatch, do not treat this occurrence as a username
                    continue;
                }

                $usernames[] = $username;
            }
        }

        return $usernames;
    }

    /**
     * Truncates a formatted message and keeps the HTML syntax intact.
     *
     * @param string $message string which shall be truncated
     * @param int $maxLength string length after truncating
     * @return  string                  truncated string
     */
    public static function truncateFormattedMessage($message, $maxLength = 1000)
    {
        $message = Regex::compile(
            '<!-- begin:parser_nonessential -->.*?<!-- end:parser_nonessential -->',
            Regex::DOT_ALL
        )->replace($message, '');

        return StringUtil::truncateHTML($message, $maxLength);
    }
}
