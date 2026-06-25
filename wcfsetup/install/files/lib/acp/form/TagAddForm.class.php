<?php

namespace wcf\acp\form;

use wcf\command\tag\CreateTag;
use wcf\command\tag\UpdateTag;
use wcf\data\DatabaseObjectBuilder;
use wcf\data\IStorableObject;
use wcf\data\tag\Tag;
use wcf\data\tag\TagBuilder;
use wcf\data\tag\TagList;
use wcf\form\AbstractDatabaseObjectBuilderForm;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\data\processor\CustomFormDataProcessor;
use wcf\system\form\builder\field\IFormField;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\form\builder\field\tag\TagFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\IFormDocument;
use wcf\system\form\builder\TemplateFormNode;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows the tag add form.
 *
 * @author      Olaf Braun, Tim Duesterhus
 * @copyright   2001-2024 WoltLab GmbH
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
        parent::createForm();

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
                        ),
                    TagFormField::create('synonyms')
                        ->available($this->formObject?->synonymFor === null)
                        ->label('wcf.acp.tag.synonyms')
                        ->saveValueCallback(
                            static fn(TagBuilder $builder, IFormField $field) => $builder->setSynonyms(
                                $field->getSaveValue() ?? []
                            )
                        ),
                    TemplateFormNode::create('tagSynonymFor')
                        ->available($this->formObject?->synonymFor !== null)
                        ->variables([
                            'synonym' => new Tag($this->formObject?->synonymFor),
                        ])
                        ->templateName('__tagFormSynonym')
                ])
        ]);
    }

    #[\Override]
    protected function finalizeForm(): void
    {
        parent::finalizeForm();

        $this->form->getDataHandler()
            ->addProcessor(
                new CustomFormDataProcessor(
                    'synonymsProcessor',
                    null,
                    static function (IFormDocument $document, array $data, IStorableObject $tag) {
                        \assert($tag instanceof Tag);

                        $synonymList = new TagList();
                        $synonymList->getConditionBuilder()->add('synonymFor = ?', [$tag->tagID]);
                        $synonymList->readObjects();
                        $data['synonyms'] = [];
                        foreach ($synonymList as $synonym) {
                            $data['synonyms'][] = $synonym->name;
                        }

                        return $data;
                    }
                )
            );
    }
}
