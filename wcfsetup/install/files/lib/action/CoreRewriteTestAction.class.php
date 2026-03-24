<?php

namespace wcf\action;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use wcf\util\HeaderUtil;

/**
 * Internal action used to run a test for url rewriting.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class CoreRewriteTestAction extends AbstractAction
{
    const AVAILABLE_DURING_OFFLINE_MODE = true;

    #[\Override]
    public function execute(): ResponseInterface
    {
        parent::execute();

        return HeaderUtil::withNoCacheHeaders(new JsonResponse(
            [
                'core_rewrite_test' => 'passed',
            ],
            200,
        ));
    }
}
