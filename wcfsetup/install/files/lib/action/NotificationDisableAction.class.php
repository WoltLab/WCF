<?php

namespace wcf\action;

use Laminas\Diactoros\Response\RedirectResponse;
use wcf\form\NotificationUnsubscribeForm;
use wcf\system\request\LinkHandler;
use wcf\util\StringUtil;

/**
 * Allows a user to disable notifications by a direct link.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Action
 * @deprecated  5.3 Replaced by NotificationUnsubscribeForm
 */
class NotificationDisableAction extends AbstractAction
{
    /**
     * event id
     * @var int
     */
    public $eventID = 0;

    /**
     * user id
     * @var int
     */
    public $userID = 0;

    /**
     * security token
     * @var string
     */
    public $token = '';

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (isset($_REQUEST['eventID'])) {
            $this->eventID = \intval($_REQUEST['eventID']);
        }
        if (isset($_REQUEST['userID'])) {
            $this->userID = \intval($_REQUEST['userID']);
        }
        if (isset($_REQUEST['token'])) {
            $this->token = StringUtil::trim($_REQUEST['token']);
        }

        return new RedirectResponse(
            LinkHandler::getInstance()->getControllerLink(
                NotificationUnsubscribeForm::class,
                [
                    'userID' => $this->userID,
                    'eventID' => $this->eventID,
                    'token' => $this->token,
                ]
            )
        );
    }
}
