<?php

namespace wcf\acp\action;

use CuyZ\Valinor\Mapper\MappingError;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\command\user\MergeUsers;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\http\Helper;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\form\builder\field\RadioButtonFormField;
use wcf\system\form\builder\NoticeFormNode;
use wcf\system\form\builder\NoticeFormNodeType;
use wcf\system\form\builder\Psr15DialogForm;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Merges two or more users into one of these users.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UserMergeAction implements RequestHandlerInterface
{
    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $users = $this->getUsers($request);
        $destinationUsers = $this->getDestinationUsers($users);
        if ($destinationUsers === []) {
            throw new PermissionDeniedException();
        }

        $form = $this->getForm($destinationUsers);

        if ($request->getMethod() === 'GET') {
            return $form->toResponse();
        } elseif ($request->getMethod() === 'POST') {
            $response = $form->validateRequest($request);
            if ($response !== null) {
                return $response;
            }

            $destinationUserID = (int)$form->getData()['data']['destinationUserID'];
            $mergedUsers = \array_values(\array_filter(
                $users,
                static fn(User $user) => $user->userID !== $destinationUserID
            ));

            (new MergeUsers($users[$destinationUserID], $mergedUsers))();

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
                        ids: positive-int[]
                    }
                    EOT
            );
        } catch (MappingError) {
            throw new IllegalLinkException();
        }

        $userIDs = \array_unique($parameters['ids']);
        if (\count($userIDs) < 2) {
            throw new IllegalLinkException();
        }

        $userList = new UserList();
        $userList->setObjectIDs($userIDs);
        $userList->readObjects();
        $users = $userList->getObjects();
        if (\count($users) !== \count($userIDs)) {
            throw new IllegalLinkException();
        }

        foreach ($users as $user) {
            if (!$user->canEdit()) {
                throw new PermissionDeniedException();
            }
        }

        return $users;
    }

    /**
     * Returns the users that can be chosen as the destination user. Merged
     * users are deleted, so every user except the destination user must be
     * deletable by the active user.
     *
     * @param array<int, User> $users
     * @return array<int, User>
     */
    private function getDestinationUsers(array $users): array
    {
        $undeletableUsers = \array_filter(
            $users,
            static fn(User $user) => !$user->canDelete()
        );

        return match (\count($undeletableUsers)) {
            0 => $users,
            1 => $undeletableUsers,
            default => [],
        };
    }

    /**
     * @param array<int, User> $destinationUsers
     */
    private function getForm(array $destinationUsers): Psr15DialogForm
    {
        $form = new Psr15DialogForm(
            static::class,
            WCF::getLanguage()->get('wcf.acp.user.merge')
        );
        $form->appendChildren([
            NoticeFormNode::create('warning')
                ->type(NoticeFormNodeType::Warning)
                ->languageItem('wcf.acp.user.merge.warning'),
            RadioButtonFormField::create('destinationUserID')
                ->label('wcf.acp.user.merge.destination')
                ->description('wcf.acp.user.merge.destination.description')
                ->options(\array_map(
                    static fn(User $user) => StringUtil::encodeHTML($user->username),
                    $destinationUsers
                ))
                ->required(),
        ]);
        $form->build();

        return $form;
    }
}
