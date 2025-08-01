<?php

namespace wcf\system\endpoint\controller\core\contact\recipients;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\contact\recipient\ContactRecipient;
use wcf\data\contact\recipient\ContactRecipientList;
use wcf\system\endpoint\GetRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\IllegalLinkException;
use wcf\system\showOrder\ShowOrderHandler;
use wcf\system\showOrder\ShowOrderItem;
use wcf\system\WCF;

/**
 * Retrieves the show order of contact recipients.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
#[GetRequest('/core/contact/recipients/show-order')]
final class GetShowOrder implements IController
{
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $this->assertRecipientCanBeSorted();

        $recipientList = new ContactRecipientList();
        $recipientList->sqlOrderBy = 'showOrder ASC';
        $recipientList->readObjects();

        $items = \array_map(
            static fn(ContactRecipient $recipient) => new ShowOrderItem(
                $recipient->recipientID,
                $recipient->getName()
            ),
            $recipientList->getObjects()
        );

        return (new ShowOrderHandler($items))->toJsonResponse();
    }

    private function assertRecipientCanBeSorted(): void
    {
        if (!\MODULE_CONTACT_FORM) {
            throw new IllegalLinkException();
        }

        WCF::getSession()->checkPermissions(["admin.contact.canManageContactForm"]);
    }
}
