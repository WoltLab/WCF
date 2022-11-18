<?php

namespace wcf\action;

use CuyZ\Valinor\Mapper\Source\Source;
use CuyZ\Valinor\Mapper\TreeMapper;
use CuyZ\Valinor\MapperBuilder;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\TextResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\data\moderation\queue\ModerationQueue;
use wcf\data\user\User;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\form\builder\field\dependency\ValueFormFieldDependency;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\form\builder\field\user\UserFormField;
use wcf\system\form\builder\FormDocument;
use wcf\system\moderation\queue\command\AssignUser;
use wcf\system\WCF;

/**
 * Assigns a user to a moderation queue entry.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Action
 */
final class ModerationQueueAssignUserAction implements RequestHandlerInterface
{
    private const PARAMETERS = <<<'EOT'
        array {
            id: positive-int
        }
        EOT;

    private TreeMapper $mapper;

    public function __construct()
    {
        $this->mapper = (new MapperBuilder())
            ->allowSuperfluousKeys()
            ->enableFlexibleCasting()
            ->mapper();
    }

    /**
     * @inheritDoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $parameters = $this->mapper->map(
            self::PARAMETERS,
            Source::array($request->getQueryParams())
        );

        $moderationQueue = new ModerationQueue($parameters['id']);
        if (!$moderationQueue->queueID) {
            throw new IllegalLinkException();
        }

        if (!$moderationQueue->canEdit(WCF::getUser())) {
            throw new PermissionDeniedException();
        }

        $form = $this->getForm($moderationQueue);

        if ($request->getMethod() === 'GET') {
            return new JsonResponse([
                'dialog' => $form->getHtml(),
                'formId' => $form->getId(),
            ]);
        } elseif ($request->getMethod() === 'POST') {
            $form->requestData($request->getParsedBody());
            $form->readValues();
            $form->validate();

            if ($form->hasValidationErrors()) {
                return new JsonResponse([
                    'dialog' => $form->getHtml(),
                    'formId' => $form->getId(),
                ]);
            }

            $data = $form->getData()['data'];

            $user = match ($data['assignee']) {
                'none' => null,
                'me' => WCF::getUser(),
                'other' => new User($data['other']),
            };

            $command = new AssignUser(
                $moderationQueue,
                $user
            );
            $command();

            if ($user === null) {
                return new JsonResponse([
                    'assignee' => null,
                ]);
            } else {
                return new JsonResponse([
                    'assignee' => [
                        'username' => $user->username,
                        'userID' => $user->userID,
                        'link' => $user->getLink(),
                    ],
                ]);
            }
        } else {
            return new TextResponse('The used HTTP method is not allowed.', 405, [
                'allow' => 'POST, GET',
            ]);
        }
    }

    private function getForm(ModerationQueue $moderationQueue): FormDocument
    {
        $form = new class extends FormDocument {
            public function validate()
            {
                return $this->traitValidate();
            }
        };
        $form->id(static::class);
        $form->appendChildren([
            SingleSelectionFormField::create('assignee')
                ->required()
                ->options([
                    'none' => 'none',
                    'me' => 'me',
                    'other' => 'other',
                ])
                ->value(
                    match ($moderationQueue->assignedUserID) {
                        WCF::getUser()->userID => 'me',
                        null => 'none',
                        default => 'other'
                    }
                ),
            UserFormField::create('other')
                ->addDependency(
                    ValueFormFieldDependency::create('other')
                        ->fieldId('assignee')
                        ->values(['other'])
                )
                ->value(
                    $moderationQueue->assignedUserID ?: []
                )
                ->required(),
        ]);
        $form->ajax();

        $form->build();

        return $form;
    }
}
