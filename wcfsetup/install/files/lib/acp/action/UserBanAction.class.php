<?php

namespace wcf\acp\action;

use CuyZ\Valinor\Mapper\MappingError;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\command\user\BanUser;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\http\Helper;
use wcf\system\exception\IllegalLinkException;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\DateFormField;
use wcf\system\form\builder\field\dependency\EmptyFormFieldDependency;
use wcf\system\form\builder\field\MultilineTextFormField;
use wcf\system\form\builder\Psr15DialogForm;
use wcf\system\user\UserAdministrationPolicy;
use wcf\system\WCF;

/**
 * Bans one or more users.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UserBanAction implements RequestHandlerInterface
{
    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $users = $this->getUsers($request);

        $form = $this->getForm();

        if ($request->getMethod() === 'GET') {
            return $form->toResponse();
        } elseif ($request->getMethod() === 'POST') {
            $response = $form->validateRequest($request);
            if ($response !== null) {
                return $response;
            }

            $data = $form->getData()['data'];
            $banExpires = 0;
            if (!(bool)$data['banNeverExpires']) {
                $banExpires = $data['banExpires'];
            }

            WCF::getDB()->beginTransaction();
            foreach ($users as $user) {
                if ($user->banned === 0) {
                    (new BanUser($user, $data['banReason'], $banExpires))();
                }
            }
            WCF::getDB()->commitTransaction();

            return new JsonResponse([]);
        } else {
            throw new \LogicException('Unreachable');
        }
    }

    /**
     * @return User[]
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
            UserAdministrationPolicy::assertCanBan($user);
        }

        return $users;
    }

    private function getForm(): Psr15DialogForm
    {
        $form = new Psr15DialogForm(
            static::class,
            WCF::getLanguage()->get('wcf.acp.user.ban')
        );
        $form->appendChildren([
            MultilineTextFormField::create('banReason')
                ->label('wcf.acp.user.banReason')
                ->description('wcf.acp.user.banReason.description')
                ->maximumLength(65000),
            BooleanFormField::create('banNeverExpires')
                ->label('wcf.acp.user.ban.neverExpires')
                ->value(true),
            DateFormField::create('banExpires')
                ->label('wcf.acp.user.ban.expires')
                ->description('wcf.acp.user.ban.expires.description')
                ->earliestDate(\TIME_NOW)
                ->required()
                ->addDependency(
                    EmptyFormFieldDependency::create('banNeverExpires')
                        ->fieldId('banNeverExpires')
                ),
        ]);
        $form->build();

        return $form;
    }
}
