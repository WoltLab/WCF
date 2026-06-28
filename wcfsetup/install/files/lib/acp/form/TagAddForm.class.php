<?php

namespace wcf\acp\form;

use wcf\command\tag\CreateTag;
use wcf\command\tag\UpdateTag;
use wcf\data\DatabaseObjectBuilder;
use wcf\data\tag\Tag;
use wcf\data\tag\TagBuilder;
use wcf\data\tag\TagList;
use wcf\form\AbstractDatabaseObjectBuilderForm;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\IFormField;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\form\builder\field\tag\TagFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\TemplateFormNode;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows the tag add form.
 *
 * @author      Olaf Braun, Tim Duesterhus, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectBuilderForm<Tag, TagBuilder>
 */
class TagAddForm extends AbstractDatabaseObjectBuilderForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.tag.add';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.content.tag.canManageTag'];

    /**
     * @inheritDoc
     */
    public $neededModules = ['MODULE_TAGGING'];

    /**
     * @inheritDoc
     */
    public string $objectEditLinkController = TagEditForm::class;

    #[\Override]
    protected function getDatabaseObjectBuilder(): TagBuilder
    {
        if ($this->formObject !== null) {
            return TagBuilder::forUpdate($this->formObject);
        }

        return TagBuilder::forCreate();
    }

    #[\Override]
    protected function getCommand(DatabaseObjectBuilder $builder): callable
    {
        if ($this->formObject !== null) {
            return new UpdateTag($builder);
        }

        return new CreateTag($builder);
    }

    #[\Override]
    protected function createForm(): void
    {
        $contentLanguages = LanguageFactory::getInstance()->getContentLanguages();

        $this->form->appendChildren([
            FormContainer::create('general')
                ->appendChildren([
                    TextFormField::create('name')
                        ->label('wcf.global.name')
                        ->required()
                        ->maximumLength(\TAGGING_MAX_TAG_LENGTH)
                        ->saveValueCallback(
                            static fn(TagBuilder $builder, IFormField $field) => $builder->setName(
                                \str_replace(',', '', StringUtil::trim($field->getSaveValue()))
                            )
                        )
                        ->loadValueCallback(static function (Tag $object, IFormField $field) {
                            $field->value($object->name);
                        })
                        ->addValidator(
                            new FormFieldValidator('duplicateTagValidator', function (TextFormField $field) {
                                $languageIDFormField = $field->getDocument()->getFormField('languageID');
                                $languageID = $languageIDFormField->getValue();

                                $tag = Tag::getTag($field->getValue(), $languageID ?? 0);
                                if ($tag !== null && $tag->tagID !== $this->formObject?->tagID) {
                                    $field->addValidationError(
                                        new FormFieldValidationError(
                                            'duplicate',
                                            'wcf.acp.tag.error.name.duplicate'
                                        )
                                    );
                                }
                            })
                        ),
                    SingleSelectionFormField::create('languageID')
                        ->label('wcf.acp.tag.languageID')
                        ->available($contentLanguages !== [])
                        ->options($contentLanguages)
                        ->value(isset($contentLanguages[WCF::getLanguage()->languageID]) ? WCF::getLanguage()->languageID : null)
                        ->immutable($this->formAction !== 'create')
                        ->required()
                        ->saveValueCallback(
                            static fn(TagBuilder $builder, IFormField $field) => $builder->setLanguageID(
                                (int)$field->getSaveValue()
                            )
                        )->loadValueCallback(static function (Tag $object, IFormField $field) {
                            $field->value($object->languageID);
                        }),
                    TagFormField::create('synonyms')
                        ->available($this->formObject?->synonymFor === null)
                        ->label('wcf.acp.tag.synonyms')
                        ->saveValueCallback(
                            static fn(TagBuilder $builder, IFormField $field) => $builder->setSynonyms(
                                $field->getSaveValue() ?? []
                            )
                        )->loadValueCallback(static function (Tag $object, IFormField $field) {
                            $synonymList = new TagList();
                            $synonymList->getConditionBuilder()->add('synonymFor = ?', [$object->getObjectID()]);
                            $synonymList->readObjects();
                            $field->value(\array_map(
                                static fn($synonym) => $synonym->name,
                                $synonymList->getObjects()
                            ));
                        }),
                    TemplateFormNode::create('tagSynonymFor')
                        ->available($this->formObject?->synonymFor !== null)
                        ->variables([
                            'synonym' => new Tag($this->formObject?->synonymFor),
                        ])
                        ->templateName('__tagFormSynonym')
                ])
        ]);
    }
}
