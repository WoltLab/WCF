<?php

namespace wcf\acp\action;

use CuyZ\Valinor\Mapper\MappingError;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\data\user\group\UserGroup;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\http\Helper;
use wcf\system\edit\EditHistoryManager;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\form\builder\field\IntegerFormField;
use wcf\system\form\builder\Psr15DialogForm;
use wcf\system\WCF;

/**
 * Reverts the changes to contents made by one or more users.
 *
 * @author      Tim Duesterhus, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UserContentRevertChangesAction implements RequestHandlerInterface
{
    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (\MODULE_EDIT_HISTORY === 0) {
            throw new IllegalLinkException();
        }
        if (!WCF::getSession()->hasPermission('admin.content.canBulkRevertContentChanges')) {
            throw new PermissionDeniedException();
        }

        $users = $this->getUsers($request);

        $form = $this->getForm();

        if ($request->getMethod() === 'GET') {
            return $form->toResponse();
        } elseif ($request->getMethod() === 'POST') {
            $response = $form->validateRequest($request);
            if ($response !== null) {
                return $response;
            }

            $timeframe = (int)$form->getData()['data']['timeframe'];

            EditHistoryManager::getInstance()->bulkRevert(\array_keys($users), $timeframe * 86400);

            return new JsonResponse([]);
        } else {
            throw new \LogicException('Unreachable');
        }
    }

    /**
     * @return array<int, User>
     */
    private function getUsers(ServerRequestInterface $request): array
    {
        try {
            $parameters = Helper::mapQueryParameters(
                $request->getQueryParams(),
                <<<'EOT'
                    array {
                        ids: non-empty-list<positive-int>
                    }
                    EOT
            );
        } catch (MappingError) {
            throw new IllegalLinkException();
        }

        $userIDs = \array_unique($parameters['ids']);

        $userList = new UserList();
        $userList->setObjectIDs($userIDs);
        $userList->readObjects();
        $users = $userList->getObjects();
        if (\count($users) !== \count($userIDs)) {
            throw new IllegalLinkException();
        }

        foreach ($users as $user) {
            if (!UserGroup::isAccessibleGroup($user->getGroupIDs())) {
                throw new PermissionDeniedException();
            }
        }

        return $users;
    }

    private function getForm(): Psr15DialogForm
    {
        $form = new Psr15DialogForm(
            static::class,
            WCF::getLanguage()->get('wcf.acp.user.revertChanges')
        );
        $form->appendChildren([
            IntegerFormField::create('timeframe')
                ->label('wcf.acp.user.revertChanges.timeframe')
                ->description('wcf.acp.user.revertChanges.timeframe.description')
                ->suffix('wcf.acp.option.suffix.days')
                ->minimum(1)
                ->value(7)
                ->required(),
        ]);
        $form->build();

        return $form;
    }
}
