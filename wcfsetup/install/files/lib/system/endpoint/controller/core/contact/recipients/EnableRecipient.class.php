<?php

namespace wcf\system\endpoint\controller\core\contact\recipients;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\contact\recipient\ContactRecipient;
use wcf\data\contact\recipient\ContactRecipientAction;
use wcf\http\Helper;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

/**
 * Enables the contact recipient with the given ID.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
#[PostRequest("/core/contact/recipients/{id:\d+}/enable")]
final class EnableRecipient implements IController
{
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $this->assertRecipientCanBeEnabled();

        $recipient = Helper::fetchObjectFromRequestParameter($variables['id'], ContactRecipient::class);

        if ($recipient->isDisabled) {
            (new ContactRecipientAction([$recipient], 'toggle'))->executeAction();
        }

        return new JsonResponse([]);
    }

    private function assertRecipientCanBeEnabled(): void
    {
        if (!\MODULE_CONTACT_FORM) {
            throw new IllegalLinkException();
        }

        WCF::getSession()->checkPermissions(["admin.contact.canManageContactForm"]);
    }
}
