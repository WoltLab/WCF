<?php

namespace wcf\system\endpoint\controller\core\smilies;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\smiley\Smiley;
use wcf\data\smiley\SmileyAction;
use wcf\http\Helper;
use wcf\system\endpoint\DeleteRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

/**
 * Deletes the smiley with the given ID.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
#[DeleteRequest("/core/smilies/{id:\d+}")]
final class DeleteSmiley implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $this->assertSmileyCanBeDeleted();

        $smiley = Helper::fetchObjectFromRequestParameter($variables['id'], Smiley::class);

        (new SmileyAction([$smiley], 'delete'))->executeAction();

        return new JsonResponse([]);
    }

    private function assertSmileyCanBeDeleted(): void
    {
        if (!\MODULE_SMILEY) {
            throw new IllegalLinkException();
        }

        WCF::getSession()->checkPermissions(["admin.content.smiley.canManageSmiley"]);
    }
}
