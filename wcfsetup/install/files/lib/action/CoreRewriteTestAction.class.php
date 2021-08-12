<?php

namespace wcf\action;

use Laminas\Diactoros\Response\JsonResponse;
use wcf\system\exception\IllegalLinkException;

/**
 * Internal action used to run a test for url rewriting.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Action
 * @since       3.1
 */
class CoreRewriteTestAction extends AbstractAction
{
    const AVAILABLE_DURING_OFFLINE_MODE = true;

    /**
     * @inheritDoc
     *
     * @throws      IllegalLinkException
     */
    public function readParameters()
    {
        parent::readParameters();

        if (!isset($_GET['uuidHash']) || !\hash_equals(\hash('sha256', WCF_UUID), $_GET['uuidHash'])) {
            throw new IllegalLinkException();
        }
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        parent::execute();

        return new JsonResponse(
            [
                'core_rewrite_test' => 'passed',
            ],
            200,
            [
                'access-control-allow-origin' => '*',
            ]
        );
    }
}
