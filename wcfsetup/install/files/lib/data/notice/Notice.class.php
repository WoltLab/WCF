<?php

namespace wcf\data\notice;

use wcf\data\condition\Condition;
use wcf\data\DatabaseObject;
use wcf\system\condition\ConditionHandler;
use wcf\system\request\IRouteController;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Represents a notice.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   int $noticeID       unique id of the notice
 * @property-read   string $noticeName     name of the notice shown in ACP
 * @property-read   string $notice         text of the notice or name of language item which contains the text
 * @property-read   int $noticeUseHtml      is `1` if the notice text will be rendered as HTML, otherwise `0`
 * @property-read   string $cssClassName       css class name(s) used for the notice HTML element
 * @property-read   int $showOrder      position of the notice in relation to the other notices
 * @property-read   int $isDisabled     is `1` if the notice is disabled and thus not shown, otherwise `0`
 * @property-read   int $isDismissible      is `1` if the notice can be dismissed by users, otherwise `0`
 */
class Notice extends DatabaseObject implements IRouteController
{
    /**
     * Available notice types.
     * @var string[]
     * @since 6.1
     */
    const TYPES = ['info', 'success', 'warning', 'error'];

    /**
     * true if the active user has dismissed the notice
     * @var bool
     */
    protected $isDismissed;

    /**
     * Returns the textual representation of the notice.
     *
     * @since   3.0
     */
    public function __toString(): string
    {
        // replace `{$username}` with the active user's name and `{$email}`
        // with the active user's email address
        $text = \strtr(WCF::getLanguage()->get($this->notice), [
            '{$username}' => $this->noticeUseHtml ? StringUtil::encodeHTML(WCF::getUser()->username) : WCF::getUser()->username,
            '{$email}' => $this->noticeUseHtml ? StringUtil::encodeHTML(WCF::getUser()->email) : WCF::getUser()->email,
        ]);

        if (!$this->noticeUseHtml) {
            $text = \nl2br(StringUtil::encodeHTML($text), false);
        }

        return $text;
    }

    /**
     * Returns the conditions of the notice.
     *
     * @return  Condition[]
     */
    public function getConditions()
    {
        return ConditionHandler::getInstance()->getConditions('com.woltlab.wcf.condition.notice', $this->noticeID);
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        return $this->noticeName;
    }

    /**
     * Returns true if the active user has dismissed the notice.
     *
     * @return  bool
     */
    public function isDismissed()
    {
        if (!$this->isDismissible) {
            return false;
        }

        if ($this->isDismissed === null) {
            if (WCF::getUser()->userID) {
                $dismissedNotices = UserStorageHandler::getInstance()->getField('dismissedNotices');
                if ($dismissedNotices === null) {
                    $sql = "SELECT  noticeID
                            FROM    wcf1_notice_dismissed
                            WHERE   userID = ?";
                    $statement = WCF::getDB()->prepare($sql);
                    $statement->execute([
                        WCF::getUser()->userID,
                    ]);

                    $noticeIDs = [];
                    while ($noticeID = $statement->fetchColumn()) {
                        $noticeIDs[] = $noticeID;

                        if ($noticeID == $this->noticeID) {
                            $this->isDismissed = true;
                        }
                    }

                    UserStorageHandler::getInstance()->update(
                        WCF::getUser()->userID,
                        'dismissedNotices',
                        \serialize($noticeIDs)
                    );
                } else {
                    $dismissedNoticeIDs = @\unserialize($dismissedNotices);
                    $this->isDismissed = \in_array($this->noticeID, $dismissedNoticeIDs);
                }
            } else {
                $dismissedNotices = WCF::getSession()->getVar('dismissedNotices');
                if ($dismissedNotices !== null) {
                    $dismissedNotices = @\unserialize($dismissedNotices);
                    $this->isDismissed = \in_array($this->noticeID, $dismissedNotices);
                } else {
                    $this->isDismissed = false;
                }
            }
        }

        return $this->isDismissed;
    }

    /**
     * @since 6.1
     */
    public function isCustom(): bool
    {
        return !\in_array($this->cssClassName, self::TYPES);
    }
}
