<?php

namespace wcf\action;

use Laminas\Diactoros\Response\JsonResponse;
use wcf\system\exception\AJAXException;
use wcf\system\exception\IllegalLinkException;
use wcf\system\session\SessionHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Deletes a specific user session.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated 6.1
 */
final class DeleteSessionAction extends AbstractSecureAction
{
    use TAJAXException;

    /**
     * @inheritDoc
     */
    public $loginRequired = true;

    /**
     * @var string
     */
    private $sessionID;

    /**
     * @inheritDoc
     */
    public function __run()
    {
        try {
            return parent::__run();
        } catch (\Throwable $e) {
            if ($e instanceof AJAXException) {
                throw $e;
            } else {
                $this->throwException($e);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (isset($_POST['sessionID'])) {
            $this->sessionID = StringUtil::trim($_POST['sessionID']);
        }

        if (empty($this->sessionID)) {
            throw new IllegalLinkException();
        }

        $found = false;
        foreach (SessionHandler::getInstance()->getUserSessions(WCF::getUser()) as $session) {
            if ($session->getSessionID() === $this->sessionID) {
                $found = true;
                break;
            }
        }

        if (!$found) {
            throw new IllegalLinkException();
        }
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        parent::execute();

        SessionHandler::getInstance()->deleteUserSession($this->sessionID);

        $this->executed();

        return new JsonResponse([
            'sessionID' => $this->sessionID,
        ]);
    }
}
