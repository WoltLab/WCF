<?php

namespace wcf\system\endpoint\controller\core\contact\recipients;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\command\contact\recipient\DisableContactRecipient;
use wcf\data\contact\recipient\ContactRecipient;
use wcf\http\Helper;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

/**
 * Disables the contact recipient with the given ID.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
#[PostRequest("/core/contact/recipients/{id:\d+}/disable")]
final class DisableRecipient implements IController
{
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $this->assertRecipientCanBeDisabled();

        $recipient = Helper::fetchObjectFromRequestParameter($variables['id'], ContactRecipient::class);

        if (!$recipient->isDisabled) {
            (new DisableContactRecipient($recipient))();
        }

        return new JsonResponse([]);
    }

    private function assertRecipientCanBeDisabled(): void
    {
        if (!\MODULE_CONTACT_FORM) {
            throw new IllegalLinkException();
        }

        WCF::getSession()->checkPermissions(["admin.contact.canManageContactForm"]);
    }
}
