<?php

namespace wcf\system\endpoint\controller\core\users;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\command\user\UpdateUser;
use wcf\data\user\User;
use wcf\data\user\UserBuilder;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * Saves the consent of the active user to always display external media.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
#[PostRequest('/core/users/user-consent')]
final class SaveUserConsent implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        if (WCF::getUser()->isGuest()) {
            throw new PermissionDeniedException();
        }

        $optionID = User::getUserOptionID('enableEmbeddedMedia');
        if ($optionID !== null) {
            new UpdateUser(
                UserBuilder::forUpdate(WCF::getUser())->setUserOption($optionID, '1')
            )();
        }

        return new JsonResponse([]);
    }
}
