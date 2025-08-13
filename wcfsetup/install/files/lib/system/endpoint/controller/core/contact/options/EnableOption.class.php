<?php

namespace wcf\system\endpoint\controller\core\contact\options;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\command\contact\option\EnableContactOption;
use wcf\data\contact\option\ContactOption;
use wcf\http\Helper;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

/**
 * Enables the contact option with the given ID.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
#[PostRequest("/core/contact/options/{id:\d+}/enable")]
final class EnableOption implements IController
{
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $this->assertOptionCanBeEnabled();

        $option = Helper::fetchObjectFromRequestParameter($variables['id'], ContactOption::class);

        if ($option->isDisabled) {
            (new EnableContactOption($option))();
        }

        return new JsonResponse([]);
    }

    private function assertOptionCanBeEnabled(): void
    {
        if (!\MODULE_CONTACT_FORM) {
            throw new IllegalLinkException();
        }

        WCF::getSession()->checkPermissions(["admin.contact.canManageContactForm"]);
    }
}
