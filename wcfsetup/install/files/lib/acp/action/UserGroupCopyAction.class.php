<?php

namespace wcf\acp\action;

use CuyZ\Valinor\Mapper\MappingError;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\acp\form\UserGroupEditForm;
use wcf\data\user\group\UserGroup;
use wcf\http\Helper;
use wcf\system\exception\IllegalLinkException;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\Psr15DialogForm;
use wcf\system\request\LinkHandler;
use wcf\system\user\group\command\CopyUserGroup;
use wcf\system\WCF;

/**
 * Form for copying a user group.
 *
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class UserGroupCopyAction implements RequestHandlerInterface
{
    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        WCF::getSession()->checkPermissions([
            'admin.user.canAddGroup',
            'admin.user.canEditGroup',
        ]);

        try {
            $parameters = Helper::mapQueryParameters(
                $request->getQueryParams(),
                <<<'EOT'
                array {
                    id: positive-int
                }
                EOT,
            );
        } catch (MappingError) {
            throw new IllegalLinkException();
        }

        $userGroup = new UserGroup($parameters['id']);
        if (!$userGroup->groupID) {
            throw new IllegalLinkException();
        }

        $form = $this->getForm();

        if ($request->getMethod() === 'GET') {
            return $form->toResponse();
        } elseif ($request->getMethod() === 'POST') {
            $response = $form->validateRequest($request);
            if ($response !== null) {
                return $response;
            }

            $data = $form->getData();

            $group = (new CopyUserGroup(
                $userGroup,
                $data['data']['copyUserGroupOptions'],
                $data['data']['copyMembers'],
                $data['data']['copyACLOptions']
            ))();

            return new JsonResponse([
                'result' => [
                    'groupID' => $group->groupID,
                    'redirectURL' => LinkHandler::getInstance()->getControllerLink(UserGroupEditForm::class, [
                        'id' => $group->groupID,
                    ]),
                ]
            ]);
        } else {
            throw new \LogicException('Unreachable');
        }
    }

    private function getForm(): Psr15DialogForm
    {
        $form = new Psr15DialogForm(
            UserGroupCopyAction::class,
            WCF::getLanguage()->get('wcf.acp.group.copy')
        );
        $form->appendChildren([
            FormContainer::create('section')
                ->appendChildren([
                    BooleanFormField::create('copyMembers')
                        ->label('wcf.acp.group.copy.copyMembers')
                        ->description('wcf.acp.group.copy.copyMembers.description')
                        ->value(false),
                    BooleanFormField::create('copyUserGroupOptions')
                        ->label('wcf.acp.group.copy.copyUserGroupOptions')
                        ->description('wcf.acp.group.copy.copyUserGroupOptions.description')
                        ->value(false),
                    BooleanFormField::create('copyACLOptions')
                        ->label('wcf.acp.group.copy.copyACLOptions')
                        ->description('wcf.acp.group.copy.copyACLOptions.description')
                        ->value(false),
                ])
        ]);

        $form->build();

        return $form;
    }
}
