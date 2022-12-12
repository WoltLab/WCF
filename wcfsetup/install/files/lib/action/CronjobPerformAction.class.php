<?php

namespace wcf\action;

use Laminas\Diactoros\Response\EmptyResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\system\cronjob\CronjobScheduler;
use wcf\system\WCF;

/**
 * Performs pending cronjobs.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Action
 * @since   6.0
 */
final class CronjobPerformAction implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        CronjobScheduler::getInstance()->executeCronjobs();

        WCF::getSession()->disableUpdate();
        WCF::getSession()->deleteIfNew();

        return new EmptyResponse();
    }
}
