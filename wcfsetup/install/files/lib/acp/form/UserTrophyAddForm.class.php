<?php

namespace wcf\acp\form;

use wcf\command\user\trophy\CreateUserTrophy;
use wcf\command\user\trophy\UpdateUserTrophy;
use wcf\data\DatabaseObjectBuilder;
use wcf\data\trophy\category\TrophyCategoryCache;
use wcf\data\trophy\TrophyCache;
use wcf\data\user\trophy\UserTrophy;
use wcf\data\user\trophy\UserTrophyBuilder;
use wcf\form\AbstractDatabaseObjectBuilderForm;
use wcf\system\exception\NamedUserException;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\dependency\NonEmptyFormFieldDependency;
use wcf\system\form\builder\field\IFormField;
use wcf\system\form\builder\field\SelectFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\user\UserFormField;
use wcf\system\l10n\L10nStorage;
use wcf\system\WCF;
use wcf\util\HtmlString;
use wcf\util\StringUtil;

/**
 * User trophy add form.
 *
 * @author  Joshua Ruesweg, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectBuilderForm<UserTrophy, UserTrophyBuilder>
 */
class UserTrophyAddForm extends AbstractDatabaseObjectBuilderForm
{
    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.trophy.canAwardTrophy'];

    /**
     * @inheritDoc
     */
    public $neededModules = ['MODULE_TROPHY'];

    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.userTrophy.add';

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        if ($this->formAction === 'create' && !$this->hasSuitableTrophy()) {
            throw new NamedUserException(HtmlString::fromSafeHtml(
                WCF::getLanguage()->getDynamicVariable('wcf.acp.trophy.error.noSuitableTrophies')
            ));
        }
    }

    #[\Override]
    protected function getDatabaseObjectBuilder(): UserTrophyBuilder
    {
        if ($this->formObject !== null) {
            return UserTrophyBuilder::forUpdate($this->formObject);
        }

        return UserTrophyBuilder::forCreate()
            ->setTime(\TIME_NOW);
    }

    #[\Override]
    protected function getCommand(DatabaseObjectBuilder $builder): callable
    {
        if ($this->formObject !== null) {
            return new UpdateUserTrophy($builder);
        }

        $field = $this->form->getNodeById('userIDs');
        \assert($field instanceof UserFormField);
        $userIDs = \array_column($field->getUsers(), 'userID');

        return static function () use ($builder, $userIDs): UserTrophy {
            // Each user receives a separate trophy, the builder is consumed by
            // the creation and therefore copied for every user.
            $userTrophy = null;
            foreach ($userIDs as $userID) {
                $userTrophy = new CreateUserTrophy((clone $builder)->setUserID($userID))();
            }

            \assert($userTrophy !== null);

            return $userTrophy;
        };
    }

    #[\Override]
    protected function createForm(): void
    {
        parent::createForm();

        $container = FormContainer::create('data');

        if ($this->formObject === null) {
            $container->appendChildren([
                UserFormField::create('userIDs')
                    ->label('wcf.acp.trophy.userTrophy.user')
                    ->description('wcf.acp.trophy.userTrophy.user.description')
                    ->multiple()
                    ->maximumMultiples(25)
                    ->required(),
                SelectFormField::create('trophyID')
                    ->label('wcf.acp.trophy')
                    ->description('wcf.acp.trophy.userTrophy.description')
                    ->options($this->getTrophyOptions(), true, false)
                    ->required()
                    ->saveValueCallback(static function (UserTrophyBuilder $builder, SelectFormField $field): void {
                        $builder->setTrophyID((int)$field->getSaveValue());
                    }),
            ]);
        }

        $container->appendChildren([
            BooleanFormField::create('useCustomDescription')
                ->label('wcf.acp.trophy.userTrophy.useCustomDescription')
                ->saveValueCallback(static function (UserTrophyBuilder $builder, BooleanFormField $field): void {
                    $useCustomDescription = (bool)$field->getSaveValue();
                    $builder->setUseCustomDescription($useCustomDescription);

                    // The dependent fields are not saved if the custom
                    // description is no longer used.
                    if (!$useCustomDescription) {
                        $builder->setTrophyUseHtml(false);
                        if ($builder->isUpdate()) {
                            $builder->setDescription([L10nStorage::MONOLINGUAL => '']);
                        }
                    }
                })
                ->loadValueCallback(static function (UserTrophy $object, IFormField $field): void {
                    $field->value($object->useCustomDescription);
                }),
            TextFormField::create('description')
                ->label('wcf.acp.trophy.description')
                ->l10n()
                ->required()
                ->saveValueCallback(static function (UserTrophyBuilder $builder, TextFormField $field): void {
                    $builder->setDescription($field->getL10nValues());
                })
                ->loadValueCallback(static function (UserTrophy $object, IFormField $field): void {
                    $description = $object->getL10nValues('description');
                    if ($description !== []) {
                        $field->value($description);
                    }
                })
                ->addDependency(
                    NonEmptyFormFieldDependency::create('useCustomDescription')
                        ->fieldId('useCustomDescription')
                ),
            BooleanFormField::create('trophyUseHtml')
                ->label('wcf.acp.trophy.trophyUseHtml')
                ->saveValueCallback(static function (UserTrophyBuilder $builder, BooleanFormField $field): void {
                    $builder->setTrophyUseHtml((bool)$field->getSaveValue());
                })
                ->loadValueCallback(static function (UserTrophy $object, IFormField $field): void {
                    $field->value($object->trophyUseHtml);
                })
                ->addDependency(
                    NonEmptyFormFieldDependency::create('useCustomDescription')
                        ->fieldId('useCustomDescription')
                ),
        ]);

        $this->form->appendChild($container);
    }

    /**
     * Returns the trophies grouped by their category, trophies that are awarded
     * automatically cannot be selected.
     *
     * @return list<array{label: string, value: string|int, depth: int, isSelectable: bool}>
     */
    private function getTrophyOptions(): array
    {
        $options = [];
        foreach (TrophyCategoryCache::getInstance()->getCategories() as $category) {
            $trophies = $category->getTrophies(true);
            if ($trophies === []) {
                continue;
            }

            $options[] = [
                'label' => StringUtil::encodeHTML($category->getTitle()),
                'value' => 'category' . $category->categoryID,
                'depth' => 0,
                'isSelectable' => false,
            ];

            foreach ($trophies as $trophy) {
                $options[] = [
                    'label' => StringUtil::encodeHTML($trophy->getTitle()),
                    'value' => $trophy->trophyID,
                    'depth' => 1,
                    'isSelectable' => $trophy->awardAutomatically === 0,
                ];
            }
        }

        return $options;
    }

    /**
     * Returns true if trophies exist that are not automatically awarded.
     */
    private function hasSuitableTrophy(): bool
    {
        foreach (TrophyCache::getInstance()->getTrophies() as $trophy) {
            if ($trophy->awardAutomatically === 0) {
                return true;
            }
        }

        return false;
    }
}
