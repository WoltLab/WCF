<?php

namespace wcf\acp\action;

use CuyZ\Valinor\Mapper\MappingError;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\acp\form\TemplateGroupAddForm;
use wcf\acp\form\TemplateGroupEditForm;
use wcf\data\template\group\TemplateGroup;
use wcf\data\template\group\TemplateGroupAction;
use wcf\data\template\TemplateAction;
use wcf\data\template\TemplateList;
use wcf\http\Helper;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\Psr15DialogForm;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Handles the copying of template groups.
 *
 * @author      Olaf Braun
 * @copyright   2001-2023 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class TemplateGroupCopyAction implements RequestHandlerInterface
{
    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (!WCF::getSession()->getPermission('admin.template.canManageTemplate')) {
            throw new PermissionDeniedException();
        }

        try {
            $queryParameters = Helper::mapQueryParameters(
                $_GET,
                <<<'EOT'
                    array {
                        id: positive-int
                    }
                    EOT
            );
            $templateGroup = new TemplateGroup($queryParameters['id']);

            if (!$templateGroup->getObjectID()) {
                throw new IllegalLinkException();
            }
        } catch (MappingError) {
            throw new IllegalLinkException();
        }

        $form = $this->getForm($templateGroup);

        if ($request->getMethod() === 'GET') {
            return $form->toResponse();
        } elseif ($request->getMethod() === 'POST') {
            $response = $form->validateRequest($request);
            if ($response !== null) {
                return $response;
            }

            $data = $form->getData()['data'];
            $data['parentTemplateGroupID'] = $templateGroup->parentTemplateGroupID ?: null;

            $returnValues = (new TemplateGroupAction([], 'create', ['data' => $data]))->executeAction();
            /** @var TemplateGroup $templateGroup */
            $newTemplateGroup = $returnValues['returnValues'];

            $templateList = new TemplateList();
            $templateList->getConditionBuilder()->add(
                "template.templateGroupID = ?",
                [$templateGroup->templateGroupID]
            );
            $templateList->readObjects();

            foreach ($templateList as $template) {
                (new TemplateAction([], 'create', [
                    'data' => [
                        'application' => $template->application,
                        'templateName' => $template->templateName,
                        'packageID' => $template->packageID,
                        'templateGroupID' => $newTemplateGroup->templateGroupID,
                    ],
                    'source' => $template->getSource(),
                ]))->executeAction();
            }

            return new JsonResponse([
                'result' => [
                    'redirectURL' => LinkHandler::getInstance()->getControllerLink(TemplateGroupEditForm::class, [
                        'id' => $newTemplateGroup->templateGroupID,
                    ]),
                ]
            ]);
        } else {
            throw new \LogicException('Unreachable');
        }
    }

    private function getForm(TemplateGroup $templateGroup): Psr15DialogForm
    {
        $form = new Psr15DialogForm(
            TemplateGroupCopyAction::class,
            WCF::getLanguage()->get('wcf.acp.template.group.copy')
        );
        $form->appendChildren([
            TextFormField::create('templateGroupName')
                ->label('wcf.global.name')
                ->required()
                ->value($templateGroup->templateGroupName)
                ->addValidator(TemplateGroupAddForm::getTemplateNameValidator()),
            TextFormField::create('templateGroupFolderName')
                ->label('wcf.acp.template.group.folderName')
                ->required()
                ->value($templateGroup->templateGroupFolderName)
                ->addValidator(TemplateGroupAddForm::getFolderNameValidator())
                ->addValidator(TemplateGroupAddForm::getUniqueFolderNameValidator()),
        ]);

        $form->build();

        return $form;
    }
}
