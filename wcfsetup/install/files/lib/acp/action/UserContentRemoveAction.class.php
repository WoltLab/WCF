<?php

namespace wcf\acp\action;

use CuyZ\Valinor\Mapper\MappingError;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\http\Helper;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\form\builder\field\MultipleSelectionFormField;
use wcf\system\form\builder\NoticeFormNode;
use wcf\system\form\builder\NoticeFormNodeType;
use wcf\system\form\builder\Psr15DialogForm;
use wcf\system\WCF;

/**
 * Selects the content of one or more users that will be removed. The removal
 * itself is performed by the `UserContentRemoveWorker` on the client side.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UserContentRemoveAction implements RequestHandlerInterface
{
    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $users = $this->getUsers($request);

        $form = $this->getForm($users);

        if ($request->getMethod() === 'GET') {
            return $form->toResponse();
        } elseif ($request->getMethod() === 'POST') {
            $response = $form->validateRequest($request);
            if ($response !== null) {
                return $response;
            }

            return new JsonResponse([
                'result' => [
                    'userIDs' => \array_keys($users),
                    'contentProviders' => \array_values($form->getData()['contentProviders']),
                ],
            ]);
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
                        id?: positive-int,
                        ids?: positive-int[]
                    }
                    EOT
            );
        } catch (MappingError) {
            throw new IllegalLinkException();
        }

        $userIDs = $parameters['ids'] ?? (isset($parameters['id']) ? [$parameters['id']] : []);
        if ($userIDs === []) {
            throw new IllegalLinkException();
        }

        $userList = new UserList();
        $userList->setObjectIDs($userIDs);
        $userList->readObjects();
        $users = $userList->getObjects();
        if (\count($users) !== \count(\array_unique($userIDs))) {
            throw new IllegalLinkException();
        }

        foreach ($users as $user) {
            if (!$user->canDelete()) {
                throw new PermissionDeniedException();
            }
        }

        return $users;
    }

    /**
     * @param array<int, User> $users
     */
    private function getForm(array $users): Psr15DialogForm
    {
        $contentProviders = \array_filter(
            ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.content.userContentProvider'),
            static fn(ObjectType $objectType) => $objectType->hidden !== '1'
        );

        $options = [];
        foreach ($contentProviders as $objectType) {
            $options[$objectType->objectType] = WCF::getLanguage()->get(
                'wcf.acp.content.provider.' . $objectType->objectType
            );
        }

        $form = new Psr15DialogForm(
            static::class,
            WCF::getLanguage()->get('wcf.acp.content.removeContent')
        );
        $form->appendChildren([
            NoticeFormNode::create('warning')
                ->type(NoticeFormNodeType::Warning)
                ->languageItem('wcf.acp.content.provider.removeContentWarning'),
            MultipleSelectionFormField::create('contentProviders')
                ->label('wcf.acp.content.provider.removeContentSectionTitle')
                ->description('wcf.acp.content.provider.removeContentInfo', [
                    'users' => $users,
                    'user' => \reset($users),
                ])
                ->options($options, false, false)
                ->value(\array_keys($options))
                ->required(),
        ]);
        $form->build();

        return $form;
    }
}
