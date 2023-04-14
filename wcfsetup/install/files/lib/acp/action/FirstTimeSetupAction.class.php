<?php

namespace wcf\acp\action;

use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\acp\form\FirstTimeSetupLicenseForm;
use wcf\acp\form\FirstTimeSetupOptionsForm;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Dispatches to the first time setup steps.
 *
 * @author Tim Duesterhus
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
final class FirstTimeSetupAction implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        WCF::getSession()->checkPermissions([
            'admin.general.canUseAcp',
        ]);

        $controller = match (\FIRST_TIME_SETUP_STATE) {
            0 => FirstTimeSetupLicenseForm::class,
            1 => FirstTimeSetupOptionsForm::class,
            2 => FirstTimeSetupOptionsEmailForm::class,
            default => FirstTimeSetupCompletedPage::class
        };

        return new RedirectResponse(LinkHandler::getInstance()->getControllerLink(
            $controller
        ), 303);
    }
}
