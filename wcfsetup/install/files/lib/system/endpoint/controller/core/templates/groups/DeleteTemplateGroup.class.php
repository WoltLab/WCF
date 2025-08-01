<?php

namespace wcf\system\endpoint\controller\core\templates\groups;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\template\group\TemplateGroup;
use wcf\data\template\group\TemplateGroupAction;
use wcf\http\Helper;
use wcf\system\endpoint\DeleteRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * Deletes the template group with the given ID.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
#[DeleteRequest('/core/templates/groups/{id:\d+}')]
final class DeleteTemplateGroup implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $group = Helper::fetchObjectFromRequestParameter($variables['id'], TemplateGroup::class);

        $this->assertTemplateGroupCanBeDeleted($group);

        (new TemplateGroupAction([$group], 'delete'))->executeAction();

        return new JsonResponse([]);
    }

    private function assertTemplateGroupCanBeDeleted(TemplateGroup $group): void
    {
        WCF::getSession()->checkPermissions(["admin.template.canManageTemplate"]);

        if ($group->isImmutable()) {
            throw new PermissionDeniedException();
        }
    }
}
