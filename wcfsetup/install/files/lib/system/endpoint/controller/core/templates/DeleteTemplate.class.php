<?php

namespace wcf\system\endpoint\controller\core\templates;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\template\Template;
use wcf\data\template\TemplateAction;
use wcf\http\Helper;
use wcf\system\endpoint\DeleteRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * Deletes the template with the given ID.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
#[DeleteRequest('/core/templates/{id:\d+}')]
final class DeleteTemplate implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $template = Helper::fetchObjectFromRequestParameter($variables['id'], Template::class);

        $this->assertTemplateCanBeDeleted($template);

        (new TemplateAction([$template], 'delete'))->executeAction();

        return new JsonResponse([]);
    }

    private function assertTemplateCanBeDeleted(Template $template): void
    {
        WCF::getSession()->checkPermissions(['admin.template.canManageTemplate']);

        if ($template->templateGroupID === null) {
            throw new PermissionDeniedException();
        }
    }
}
