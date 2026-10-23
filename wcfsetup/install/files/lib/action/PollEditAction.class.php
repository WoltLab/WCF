<?php

namespace wcf\action;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\command\poll\CreatePoll;
use wcf\command\poll\UpdatePoll;
use wcf\data\poll\Poll;
use wcf\data\poll\PollBuilder;
use wcf\http\Helper;
use wcf\system\acl\simple\SimpleAclHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\acl\simple\SimpleAclFormField;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\DateFormField;
use wcf\system\form\builder\field\IFormField;
use wcf\system\form\builder\field\IntegerFormField;
use wcf\system\form\builder\field\poll\PollOptionListFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\IFormDocument;
use wcf\system\form\builder\Psr15BuilderDialogForm;
use wcf\system\l10n\L10nStorage;
use wcf\system\WCF;

/**
 * Creates or edits a standalone poll in a dialog.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class PollEditAction implements RequestHandlerInterface
{
    private ?Poll $poll = null;

    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $parameters = Helper::mapQueryParameters(
            $request->getQueryParams(),
            <<<'EOT'
                array {
                    id?: positive-int
                }
                EOT
        );

        if (isset($parameters['id'])) {
            $poll = new Poll($parameters['id']);
            if ($poll->isNil()) {
                throw new IllegalLinkException();
            }
            if (!$poll->canManage()) {
                throw new PermissionDeniedException();
            }

            $this->poll = $poll;
        } elseif (\MODULE_POLL === 0 || !WCF::getSession()->hasPermission('user.poll.canCreatePoll')) {
            throw new PermissionDeniedException();
        }

        $form = $this->getForm($request->getMethod() === 'GET');

        if ($request->getMethod() === 'GET') {
            return $form->toResponse();
        } elseif ($request->getMethod() === 'POST') {
            $response = $form->validateRequest($request);
            if ($response !== null) {
                return $response;
            }

            if ($this->poll === null) {
                $builder = PollBuilder::forCreate()
                    ->setUser(WCF::getUser()->userID, WCF::getUser()->username)
                    ->setTime(\TIME_NOW);
            } else {
                $builder = PollBuilder::forUpdate($this->poll);
            }

            $form->applyValuesToBuilder($builder);
            $builder->setMultilingual($this->hasL10nValues($form));

            if ($this->poll === null) {
                $poll = (new CreatePoll($builder))();
            } else {
                $poll = (new UpdatePoll($builder))();
            }

            return new JsonResponse([
                'result' => [
                    'pollID' => $poll->pollID,
                    'question' => $poll->getQuestion(),
                ],
            ]);
        } else {
            throw new \LogicException('Unreachable');
        }
    }

    private function getForm(bool $loadValues): Psr15BuilderDialogForm
    {
        $form = new Psr15BuilderDialogForm(
            static::class,
            WCF::getLanguage()->get($this->poll === null ? 'wcf.poll.add' : 'wcf.poll.edit')
        );

        $poll = $this->poll;

        $form->appendChildren([
            FormContainer::create('general')
                ->appendChildren([
                    TextFormField::create('question')
                        ->label('wcf.poll.question')
                        ->l10n()
                        ->required()
                        ->maximumLength(255)
                        ->saveValueCallback(static function (PollBuilder $builder, TextFormField $field): void {
                            $builder->setQuestion($field->getL10nValues());
                        })
                        ->loadValueCallback(static function (Poll $object, IFormField $field): void {
                            $field->value($object->getL10nValues('question'));
                        }),
                    PollOptionListFormField::create('options')
                        ->required()
                        ->saveValueCallback(static function (PollBuilder $builder, PollOptionListFormField $field): void {
                            $builder->setOptions($field->getValue());
                        })
                        ->loadValueCallback(static function (Poll $object, IFormField $field): void {
                            $field->value(\array_values($object->getOptions()));
                        }),
                ]),
            FormContainer::create('settings')
                ->label('wcf.global.settings')
                ->appendChildren([
                    DateFormField::create('endTime')
                        ->label('wcf.poll.endTime')
                        ->supportTime()
                        ->addValidator(new FormFieldValidator(
                            'futureTime',
                            static function (DateFormField $field) use ($poll): void {
                                $endTime = (int)$field->getSaveValue();
                                if ($endTime === 0 || $endTime > \TIME_NOW) {
                                    return;
                                }

                                // A poll that has already ended may keep its end time.
                                if ($poll !== null && $poll->endTime === $endTime) {
                                    return;
                                }

                                $field->addValidationError(new FormFieldValidationError(
                                    'invalid',
                                    'wcf.poll.endTime.error.invalid'
                                ));
                            }
                        ))
                        ->saveValueCallback(static function (PollBuilder $builder, DateFormField $field): void {
                            $builder->setEndTime((int)$field->getSaveValue());
                        })
                        ->loadValueCallback(static function (Poll $object, IFormField $field): void {
                            $field->value($object->endTime);
                        }),
                    IntegerFormField::create('maxVotes')
                        ->label('wcf.poll.maxVotes')
                        ->minimum(1)
                        ->maximum(\POLL_MAX_OPTIONS)
                        ->value(1)
                        ->addValidator(new FormFieldValidator(
                            'maxVotes',
                            static function (IntegerFormField $field): void {
                                $optionsField = $field->getDocument()->getNodeById('options');
                                \assert($optionsField instanceof PollOptionListFormField);

                                if ($field->getSaveValue() > \count($optionsField->getValue())) {
                                    $field->addValidationError(new FormFieldValidationError(
                                        'invalid',
                                        'wcf.poll.maxVotes.error.invalid'
                                    ));
                                }
                            }
                        ))
                        ->saveValueCallback(static function (PollBuilder $builder, IntegerFormField $field): void {
                            $builder->setMaxVotes($field->getSaveValue());
                        })
                        ->loadValueCallback(static function (Poll $object, IFormField $field): void {
                            $field->value($object->maxVotes);
                        }),
                    BooleanFormField::create('isChangeable')
                        ->label('wcf.poll.isChangeable')
                        ->saveValueCallback(static function (PollBuilder $builder, BooleanFormField $field): void {
                            $builder->setIsChangeable($field->getSaveValue() !== 0);
                        })
                        ->loadValueCallback(static function (Poll $object, IFormField $field): void {
                            $field->value($object->isChangeable);
                        }),
                    // The visibility of the participants cannot be changed after creation.
                    BooleanFormField::create('isPublic')
                        ->label('wcf.poll.isPublic')
                        ->available(
                            $this->poll === null
                            && WCF::getSession()->hasPermission('user.poll.canStartPublicPoll')
                        )
                        ->saveValueCallback(static function (PollBuilder $builder, BooleanFormField $field): void {
                            $builder->setIsPublic($field->getSaveValue() !== 0);
                        }),
                    BooleanFormField::create('resultsRequireVote')
                        ->label('wcf.poll.resultsRequireVote')
                        ->description('wcf.poll.resultsRequireVote.description')
                        ->saveValueCallback(static function (PollBuilder $builder, BooleanFormField $field): void {
                            $builder->setResultsRequireVote($field->getSaveValue() !== 0);
                        })
                        ->loadValueCallback(static function (Poll $object, IFormField $field): void {
                            $field->value($object->resultsRequireVote);
                        }),
                    BooleanFormField::create('resultsRequireEnd')
                        ->label('wcf.poll.resultsRequireEnd')
                        ->description('wcf.poll.resultsRequireEnd.description')
                        ->addValidator(new FormFieldValidator(
                            'endTimeRequired',
                            static function (BooleanFormField $field): void {
                                if ($field->getSaveValue() === 0) {
                                    return;
                                }

                                $endTimeField = $field->getDocument()->getNodeById('endTime');
                                \assert($endTimeField instanceof DateFormField);

                                // A malformed end time is already reported by the field itself.
                                if ($endTimeField->getValidationErrors() !== []) {
                                    return;
                                }

                                if ($endTimeField->getValue() === null) {
                                    $field->addValidationError(new FormFieldValidationError(
                                        'endTimeRequired',
                                        'wcf.poll.resultsRequireEnd.error.endTimeRequired'
                                    ));
                                }
                            }
                        ))
                        ->saveValueCallback(static function (PollBuilder $builder, BooleanFormField $field): void {
                            $builder->setResultsRequireEnd($field->getSaveValue() !== 0);
                        })
                        ->loadValueCallback(static function (Poll $object, IFormField $field): void {
                            $field->value($object->resultsRequireEnd);
                        }),
                    BooleanFormField::create('sortByVotes')
                        ->label('wcf.poll.sortByVotes')
                        ->saveValueCallback(static function (PollBuilder $builder, BooleanFormField $field): void {
                            $builder->setSortByVotes($field->getSaveValue() !== 0);
                        })
                        ->loadValueCallback(static function (Poll $object, IFormField $field): void {
                            $field->value($object->sortByVotes);
                        }),
                ]),
            FormContainer::create('access')
                ->label('wcf.poll.access')
                ->appendChildren([
                    SimpleAclFormField::create('acl')
                        ->saveValueCallback(static function (PollBuilder $builder, SimpleAclFormField $field): void {
                            $value = $field->getValue();
                            if (!\is_array($value) || $value === []) {
                                return;
                            }

                            $builder->setAclValues([
                                'allowAll' => (bool)($value['allowAll'] ?? true),
                                'user' => \array_values(\array_map(\intval(...), $value['user'] ?? [])),
                                'group' => \array_values(\array_map(\intval(...), $value['group'] ?? [])),
                            ]);
                        })
                        ->loadValueCallback(static function (Poll $object, IFormField $field): void {
                            $field->value(
                                SimpleAclHandler::getInstance()->getValues('com.woltlab.wcf.poll', $object->pollID)
                            );
                        }),
                ]),
        ]);

        // The values must be loaded after building the form, because the l10n
        // fields reset their values when they are populated.
        if ($this->poll === null) {
            $form->formMode(IFormDocument::FORM_MODE_CREATE);
            $form->build();
        } else {
            $form->formMode(IFormDocument::FORM_MODE_UPDATE);
            $form->build();
            $form->updatedObject($this->poll, $loadValues);
        }

        return $form;
    }

    /**
     * Returns true if the question or any option has been provided per language.
     */
    private function hasL10nValues(IFormDocument $form): bool
    {
        $questionField = $form->getNodeById('question');
        \assert($questionField instanceof TextFormField);
        if (!isset($questionField->getL10nValues()[L10nStorage::MONOLINGUAL])) {
            return true;
        }

        $optionsField = $form->getNodeById('options');
        \assert($optionsField instanceof PollOptionListFormField);

        return $optionsField->hasL10nValues();
    }
}
