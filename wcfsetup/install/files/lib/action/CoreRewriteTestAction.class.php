<?php

namespace wcf\action;

use Laminas\Diactoros\Response\JsonResponse;

/**
 * Internal action used to run a test for url rewriting.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       3.1
 */
final class CoreRewriteTestAction extends AbstractAction
{
    const AVAILABLE_DURING_OFFLINE_MODE = true;

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
        );
    }
}
