<?php

namespace wcf\acp\action;

use CuyZ\Valinor\Mapper\MappingError;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\http\Helper;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\form\builder\field\dependency\ValueFormFieldDependency;
use wcf\system\form\builder\field\RadioButtonFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\Psr15DialogForm;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Exports the email addresses of one or more users. The file is assembled on
 * the server and downloaded on the client side.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UserEmailAddressExportAction implements RequestHandlerInterface
{
    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (!WCF::getSession()->hasPermission('admin.user.canEditMailAddress')) {
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

            $data = $form->getData()['data'];
            $fileType = (string)$data['fileType'];
            if ($fileType === 'xml') {
                $content = $this->getXml($users);
            } else {
                $content = $this->getCsv($users, (string)$data['separator'], (string)$data['textSeparator']);
            }

            return new JsonResponse([
                'result' => [
                    'filename' => 'export.' . $fileType,
                    'mimeType' => 'text/' . $fileType,
                    'content' => $content,
                ],
            ]);
        } else {
            throw new \LogicException('Unreachable');
        }
    }

    /**
     * Returns the users sorted by their email address.
     *
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
        $userList->sqlOrderBy = 'user_table.email';
        $userList->readObjects();
        $users = $userList->getObjects();
        if (\count($users) !== \count($userIDs)) {
            throw new IllegalLinkException();
        }

        return $users;
    }

    /**
     * @param array<int, User> $users
     */
    private function getCsv(array $users, string $separator, string $textSeparator): string
    {
        $escape = static fn(string $value) => $textSeparator
            . \str_replace($textSeparator, $textSeparator . $textSeparator, $value)
            . $textSeparator;

        $content = '';
        foreach ($users as $user) {
            $content .= $escape($user->username) . $separator . $escape($user->email) . "\n";
        }

        return $content;
    }

    /**
     * @param array<int, User> $users
     */
    private function getXml(array $users): string
    {
        $content = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<users>\n";
        foreach ($users as $user) {
            $content .= \sprintf(
                "<user><username><![CDATA[%s]]></username><email><![CDATA[%s]]></email></user>\n",
                StringUtil::escapeCDATA($user->username),
                StringUtil::escapeCDATA($user->email)
            );
        }
        $content .= '</users>';

        return $content;
    }

    private function getForm(): Psr15DialogForm
    {
        $form = new Psr15DialogForm(
            static::class,
            WCF::getLanguage()->get('wcf.acp.user.exportEmailAddress')
        );
        $form->appendChildren([
            RadioButtonFormField::create('fileType')
                ->label('wcf.acp.user.exportEmailAddress.fileType')
                ->options([
                    'csv' => 'wcf.acp.user.exportEmailAddress.fileType.csv',
                    'xml' => 'wcf.acp.user.exportEmailAddress.fileType.xml',
                ])
                ->value('csv')
                ->required(),
            TextFormField::create('separator')
                ->label('wcf.acp.user.exportEmailAddress.separator')
                ->value(',')
                ->addDependency(
                    ValueFormFieldDependency::create('fileType')
                        ->fieldId('fileType')
                        ->values(['csv'])
                ),
            TextFormField::create('textSeparator')
                ->label('wcf.acp.user.exportEmailAddress.textSeparator')
                ->value('"')
                ->addDependency(
                    ValueFormFieldDependency::create('fileType')
                        ->fieldId('fileType')
                        ->values(['csv'])
                ),
        ]);
        $form->build();

        return $form;
    }
}
