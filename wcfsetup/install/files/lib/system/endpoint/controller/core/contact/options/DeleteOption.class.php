<?php

namespace wcf\system\endpoint\controller\core\contact\options;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\contact\option\ContactOption;
use wcf\data\contact\option\ContactOptionAction;
use wcf\http\Helper;
use wcf\system\endpoint\DeleteRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * Deletes the contact option with the given ID.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
#[DeleteRequest("/core/contact/options/{id:\d+}")]
final class DeleteOption implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $option = Helper::fetchObjectFromRequestParameter($variables['id'], ContactOption::class);

        $this->assertOptionCanBeDeleted($option);

        (new ContactOptionAction([$option], 'delete'))->executeAction();

        return new JsonResponse([]);
    }

    private function assertOptionCanBeDeleted(ContactOption $option): void
    {
        if (!\MODULE_CONTACT_FORM) {
            throw new IllegalLinkException();
        }

        WCF::getSession()->checkPermissions(["admin.contact.canManageContactForm"]);

        if (!$option->canDelete()) {
            throw new PermissionDeniedException();
        }
    }
}
