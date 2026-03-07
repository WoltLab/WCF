<?php

namespace wcf\system\endpoint\controller\core\contact\recipients;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\contact\recipient\ContactRecipient;
use wcf\data\contact\recipient\ContactRecipientAction;
use wcf\http\Helper;
use wcf\system\endpoint\DeleteRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * Deletes the contact recipient with the given ID.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
#[DeleteRequest("/core/contact/recipients/{id:\d+}")]
final class DeleteRecipient implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $recipient = Helper::fetchObjectFromRequestParameter($variables['id'], ContactRecipient::class);

        $this->assertRecipientCanBeDeleted($recipient);

        (new ContactRecipientAction([$recipient], 'delete'))->executeAction();

        return new JsonResponse([]);
    }

    private function assertRecipientCanBeDeleted(ContactRecipient $recipient): void
    {
        if (!\MODULE_CONTACT_FORM) {
            throw new IllegalLinkException();
        }

        WCF::getSession()->checkPermissions(["admin.contact.canManageContactForm"]);

        if ($recipient->originIsSystem) {
            throw new PermissionDeniedException();
        }
    }
}
