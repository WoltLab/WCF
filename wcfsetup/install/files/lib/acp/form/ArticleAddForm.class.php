<?php

namespace wcf\acp\form;

use wcf\command\article\CreateArticle;
use wcf\command\article\UpdateArticle;
use wcf\data\article\Article;
use wcf\data\article\ArticleBuilder;
use wcf\data\article\category\ArticleCategory;
use wcf\data\article\content\ArticleContent;
use wcf\data\category\CategoryNodeTree;
use wcf\data\DatabaseObjectBuilder;
use wcf\data\language\Language;
use wcf\data\user\User;
use wcf\form\AbstractDatabaseObjectBuilderForm;
use wcf\system\cache\builder\ArticleCategoryLabelCacheBuilder;
use wcf\system\exception\NamedUserException;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\container\TabFormContainer;
use wcf\system\form\builder\container\TabMenuFormContainer;
use wcf\system\form\builder\container\wysiwyg\WysiwygFormContainer;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\DateFormField;
use wcf\system\form\builder\field\dependency\ValueFormFieldDependency;
use wcf\system\form\builder\field\HiddenFormField;
use wcf\system\form\builder\field\IFormField;
use wcf\system\form\builder\field\label\LabelFormField;
use wcf\system\form\builder\field\media\SingleMediaSelectionFormField;
use wcf\system\form\builder\field\MultilineTextFormField;
use wcf\system\form\builder\field\RadioButtonFormField;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\form\builder\field\tag\TagFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\TitleFormField;
use wcf\system\form\builder\field\user\UserFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\field\wysiwyg\WysiwygAttachmentFormField;
use wcf\system\form\builder\field\wysiwyg\WysiwygFormField;
use wcf\system\label\LabelHandler;
use wcf\system\label\object\ArticleLabelObjectHandler;
use wcf\system\language\LanguageFactory;
use wcf\system\request\LinkHandler;
use wcf\system\tagging\TagEngine;
use wcf\system\WCF;
use wcf\util\HeaderUtil;
use wcf\util\HtmlString;
use wcf\util\StringUtil;

/**
 * Shows the article add form.
 *
 * @author      Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectBuilderForm<Article, ArticleBuilder>
 */
class ArticleAddForm extends AbstractDatabaseObjectBuilderForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.article.add';

    /**
     * @inheritDoc
     */
    public $neededModules = ['MODULE_ARTICLE'];

    /**
     * @inheritDoc
     */
    public $neededPermissions = [
        'admin.content.article.canManageArticle',
        'admin.content.article.canManageOwnArticles',
        'admin.content.article.canContributeArticle',
    ];

    /**
     * @inheritDoc
     */
    public string $objectEditLinkController = ArticleEditForm::class;

    /**
     * true if created article is multi-lingual
     */
    public int $isMultilingual = 0;

    public int $categoryID = 0;

    protected CategoryNodeTree $categoryNodeTree;

    #[\Override]
    public function readParameters(): void
    {
        parent::readParameters();

        $this->categoryNodeTree = new CategoryNodeTree('com.woltlab.wcf.article.category');
        if (\iterator_count($this->categoryNodeTree->getIterator()) === 0) {
            throw new NamedUserException(
                HtmlString::fromSafeHtml(WCF::getLanguage()->getDynamicVariable('wcf.acp.article.category.error.noCategories'))
            );
        }

        if (isset($_REQUEST['categoryID'])) {
            $this->categoryID = \intval($_REQUEST['categoryID']);
        }

        $this->readMultilingualSetting();
    }

    /**
     * Reads basic article parameters controlling i18n.
     */
    protected function readMultilingualSetting(): void
    {
        if (!empty($_REQUEST['isMultilingual']) || !empty($_REQUEST['article_isMultilingual'])) {
            $this->isMultilingual = 1;
        }

        // work-around to force adding article via dialog overlay
        $availableLanguages = LanguageFactory::getInstance()->getLanguages();
        if (\count($availableLanguages) > 1 && empty($_POST) && !isset($_REQUEST['isMultilingual'])) {
            $parameters = ['showArticleAddDialog' => 1];
            if ($this->categoryID !== 0) {
                $parameters['categoryID'] = $this->categoryID;
            }
            HeaderUtil::redirect(LinkHandler::getInstance()->getLink('ArticleList', $parameters));

            exit;
        }
    }

    #[\Override]
    protected function createForm(): void
    {
        parent::createForm();

        $this->form->prefix('article');

        $labelFormFields = $this->createLabelFormFields();

        $canManage = WCF::getSession()->hasPermission('admin.content.article.canManageArticle')
            || WCF::getSession()->hasPermission('admin.content.article.canManageOwnArticles');

        $this->form->appendChildren([
            HiddenFormField::create('isMultilingual')
                ->value($this->isMultilingual)
                ->saveValueCallback(static function (ArticleBuilder $builder, IFormField $field) {
                    $builder->setIsMultilingual((bool)$field->getSaveValue());
                }),
            FormContainer::create('information')
                ->label('wcf.global.category')
                ->appendChildren([
                    SingleSelectionFormField::create('categoryID')
                        ->filterable(\iterator_count($this->categoryNodeTree->getIterator()) > 10)
                        ->required()
                        ->options($this->categoryNodeTree, true)
                        ->value($this->categoryID ?: null)
                        ->label('wcf.global.category')
                        ->addValidator(new FormFieldValidator('accessible', static function (IFormField $field) {
                            $category = ArticleCategory::getCategory($field->getSaveValue());
                            if ($category === null || !$category->isAccessible()) {
                                $field->addValidationError(new FormFieldValidationError('invalid'));
                            }
                        }))
                        ->saveValueCallback(static function (ArticleBuilder $builder, IFormField $field) {
                            $builder->setCategory(ArticleCategory::getCategory((int)$field->getSaveValue()));
                        })
                        ->loadValueCallback(static function (Article $object, IFormField $field) {
                            $field->value($object->categoryID);
                        }),
                    ...$labelFormFields,
                    UserFormField::create('userID')
                        ->label('wcf.acp.article.author')
                        ->required()
                        ->value(WCF::getUser()->userID)
                        ->saveValueCallback(static function (ArticleBuilder $builder, UserFormField $field) {
                            $builder->setUser(new User((int)$field->getSaveValue()));
                        })
                        ->loadValueCallback(static function (Article $object, IFormField $field) {
                            $field->value($object->userID);
                        }),
                    DateFormField::create('time')
                        ->supportTime()
                        ->required()
                        ->label('wcf.global.date')
                        ->value(\TIME_NOW)
                        ->addValidator(new FormFieldValidator('futureCheck', function (IFormField $field) {
                            $statusField = $field->getDocument()->getFormField('publicationStatus');
                            $status = $statusField !== null
                                ? (int)$statusField->getSaveValue()
                                : Article::UNPUBLISHED;
                            if ($status === Article::PUBLISHED && (int)$field->getSaveValue() > \TIME_NOW) {
                                $field->addValidationError(new FormFieldValidationError('invalid'));
                            }
                        }))
                        ->saveValueCallback(static function (ArticleBuilder $builder, IFormField $field) {
                            $builder->setTime((int)$field->getSaveValue());
                        })
                        ->loadValueCallback(static function (Article $object, IFormField $field) {
                            $field->value($object->time);
                        }),
                    RadioButtonFormField::create('publicationStatus')
                        ->label('wcf.acp.article.publicationStatus')
                        ->options([
                            Article::PUBLISHED => 'wcf.acp.article.publicationStatus.published',
                            Article::UNPUBLISHED => 'wcf.acp.article.publicationStatus.unpublished',
                            Article::DELAYED_PUBLICATION => 'wcf.acp.article.publicationStatus.delayed',
                        ])
                        ->available($canManage)
                        ->value(Article::PUBLISHED)
                        ->required()
                        // The publication status drives the publication date; the
                        // date is only relevant for a delayed publication.
                        ->saveValueCallback(static function (ArticleBuilder $builder, IFormField $field) {
                            $status = (int)$field->getSaveValue();
                            $builder->setPublicationStatus($status);

                            $dateField = $field->getDocument()->getFormField('publicationDate');
                            $builder->setPublicationDate(
                                $status === Article::DELAYED_PUBLICATION
                                    && $dateField !== null
                                    && $dateField->getSaveValue()
                                    ? (int)$dateField->getSaveValue()
                                    : 0
                            );
                        })
                        ->loadValueCallback(static function (Article $object, IFormField $field) {
                            $field->value($object->publicationStatus);
                        }),
                    DateFormField::create('publicationDate')
                        ->supportTime()
                        ->required()
                        ->label('wcf.acp.article.publicationDate')
                        ->addDependency(
                            ValueFormFieldDependency::create('publicationStatus')
                                ->fieldId('publicationStatus')
                                ->values([Article::DELAYED_PUBLICATION])
                        )
                        ->addValidator(new FormFieldValidator('futureCheck', static function (IFormField $field) {
                            if (
                                $field->getValue() !== null
                                && $field->getValue() !== ''
                                && (int)$field->getSaveValue() < \TIME_NOW
                            ) {
                                $field->addValidationError(new FormFieldValidationError('invalid'));
                            }
                        }))
                        ->loadValueCallback(static function (Article $object, IFormField $field) {
                            if ($object->publicationDate !== 0) {
                                $field->value($object->publicationDate);
                            }
                        }),
                    BooleanFormField::create('enableComments')
                        ->label('wcf.acp.article.enableComments')
                        ->value(\ARTICLE_ENABLE_COMMENTS_DEFAULT_VALUE)
                        ->saveValueCallback(static function (ArticleBuilder $builder, IFormField $field) {
                            $builder->setEnableComments((bool)$field->getSaveValue());
                        })
                        ->loadValueCallback(static function (Article $object, IFormField $field) {
                            $field->value((bool)$object->enableComments);
                        }),
                ]),
        ]);

        if ($this->isMultilingual !== 0) {
            $this->createMultilingualForm();
        } else {
            $this->createMonolingualForm();
        }
    }

    /**
     * Creates form fields for label selection.
     *
     * @return LabelFormField[]
     */
    protected function createLabelFormFields(): array
    {
        $labelFormFields = [];

        // Invert mapping: categoryID => [groupIDs] -> groupID => [categoryIDs]
        $categoryIDsByLabelGroup = [];
        foreach (ArticleCategoryLabelCacheBuilder::getInstance()->getData() as $categoryID => $groupIDs) {
            foreach ($groupIDs as $groupID) {
                $categoryIDsByLabelGroup[$groupID][] = $categoryID;
            }
        }

        $optionID = LabelHandler::getInstance()->getOptionID('canSetLabel');
        foreach ($categoryIDsByLabelGroup as $groupID => $categoryIDs) {
            $labelGroup = LabelHandler::getInstance()->getLabelGroup($groupID);
            if ($labelGroup === null) {
                continue;
            }

            $labelFormFields[] = LabelFormField::create('labelIDs' . $groupID)
                ->objectProperty('labelIDs')
                ->objectType('com.woltlab.wcf.article')
                ->available(
                    !$labelGroup->hasPermissions()
                        || $labelGroup->getPermission($optionID)
                )
                ->required($labelGroup->forceSelection !== 0)
                ->addDependency(
                    ValueFormFieldDependency::create('categoryID')
                        ->fieldId('categoryID')
                        ->values($categoryIDs)
                )
                ->labelGroup($labelGroup)
                ->saveValueCallback(static function (ArticleBuilder $builder, IFormField $field) {
                    // `-1` and `0` are special values that are irrelevant for saving.
                    $labelID = (int)$field->getSaveValue();
                    if ($labelID > 0) {
                        $builder->setLabelID($labelID);
                    }
                })
                ->loadValueCallback(static function (Article $object, LabelFormField $field) use ($groupID) {
                    foreach ($object->getLabels() as $label) {
                        if ($label->groupID === $groupID) {
                            $field->value($label->labelID);
                            break;
                        }
                    }
                });
        }

        return $labelFormFields;
    }

    protected function createMonolingualForm(): void
    {
        $this->form->appendChildren([
            FormContainer::create('contentSection')
                ->label('wcf.acp.article.content')
                ->appendChildren($this->getContentFormFields(null)),
            $this->createContentContainer(null),
        ]);
    }

    protected function createMultilingualForm(): void
    {
        $tabContainer = TabMenuFormContainer::create('languages');
        $this->form->appendChild($tabContainer);

        foreach (LanguageFactory::getInstance()->getLanguages() as $language) {
            $lc = $language->languageCode;

            $tabContainer->appendChild(
                TabFormContainer::create("language_{$lc}")
                    ->label($language->languageName)
                    ->appendChildren([
                        FormContainer::create("contentSection_{$lc}")
                            ->appendChildren($this->getContentFormFields($language)),
                        $this->createContentContainer($language),
                    ])
            );
        }
    }

    /**
     * Returns the content form fields for the given language. Pass `null` for the
     * monolingual content.
     *
     * @return AbstractFormField[]
     */
    protected function getContentFormFields(?Language $language): array
    {
        $languageID = $language?->languageID;
        $suffix = $language !== null ? "_{$language->languageCode}" : '';

        $fields = [];
        if (WCF::getSession()->hasPermission('admin.content.cms.canUseMedia')) {
            $fields[] = SingleMediaSelectionFormField::create("imageID{$suffix}")
                ->label('wcf.acp.article.image')
                ->imageOnly()
                ->saveValueCallback(function (ArticleBuilder $builder, IFormField $field) use ($languageID) {
                    $value = $field->getSaveValue();
                    $builder->getArticleContentBuilder($languageID)->setImageID($value ? (int)$value : null);
                })
                ->loadValueCallback(function (Article $object, IFormField $field) use ($languageID) {
                    $field->value($this->getArticleContent($object, $languageID)?->imageID);
                });
            $fields[] = SingleMediaSelectionFormField::create("teaserImageID{$suffix}")
                ->label('wcf.acp.article.teaserImage')
                ->imageOnly()
                ->saveValueCallback(function (ArticleBuilder $builder, IFormField $field) use ($languageID) {
                    $value = $field->getSaveValue();
                    $builder->getArticleContentBuilder($languageID)->setTeaserImageID($value ? (int)$value : null);
                })
                ->loadValueCallback(function (Article $object, IFormField $field) use ($languageID) {
                    $field->value($this->getArticleContent($object, $languageID)?->teaserImageID);
                });
        }

        $fields[] = TitleFormField::create("title{$suffix}")
            ->required()
            ->maximumLength(255)
            ->saveValueCallback(function (ArticleBuilder $builder, IFormField $field) use ($languageID) {
                $builder->getArticleContentBuilder($languageID)->setTitle((string)$field->getSaveValue());
            })
            ->loadValueCallback(function (Article $object, IFormField $field) use ($languageID) {
                $field->value($this->getArticleContent($object, $languageID)?->title);
            });
        $fields[] = TextFormField::create("slug{$suffix}")
            ->label('wcf.acp.article.slug')
            ->description('wcf.acp.article.slug.description')
            ->maximumLength(255)
            ->addValidator($this->getSlugValidator($languageID))
            ->saveValueCallback(function (ArticleBuilder $builder, IFormField $field) use ($languageID) {
                $builder->getArticleContentBuilder($languageID)
                    ->setSlug(\mb_strtolower(StringUtil::trim((string)$field->getSaveValue())));
            })
            ->loadValueCallback(function (Article $object, IFormField $field) use ($languageID) {
                $field->value($this->getArticleContent($object, $languageID)?->slug);
            });
        $fields[] = MultilineTextFormField::create("teaser{$suffix}")
            ->label('wcf.acp.article.teaser')
            ->saveValueCallback(function (ArticleBuilder $builder, IFormField $field) use ($languageID) {
                $builder->getArticleContentBuilder($languageID)->setTeaser((string)$field->getSaveValue());
            })
            ->loadValueCallback(function (Article $object, IFormField $field) use ($languageID) {
                $field->value($this->getArticleContent($object, $languageID)?->teaser);
            });
        $fields[] = TagFormField::create("tags{$suffix}")
            ->available(\MODULE_TAGGING !== 0)
            ->objectType('com.woltlab.wcf.article')
            ->saveValueCallback(function (ArticleBuilder $builder, IFormField $field) use ($languageID) {
                $builder->getArticleContentBuilder($languageID)->setTags($field->getSaveValue() ?? []);
            })
            ->loadValueCallback(function (Article $object, IFormField $field) use ($languageID) {
                $content = $this->getArticleContent($object, $languageID);
                if ($content === null) {
                    return;
                }

                $field->value(\array_map(
                    static fn($tag) => $tag->name,
                    TagEngine::getInstance()->getObjectTags(
                        'com.woltlab.wcf.article',
                        $content->articleContentID,
                        [$content->languageID ?: LanguageFactory::getInstance()->getDefaultLanguageID()]
                    )
                ));
            });
        $fields[] = TextFormField::create("metaTitle{$suffix}")
            ->label('wcf.acp.article.metaTitle')
            ->maximumLength(255)
            ->saveValueCallback(function (ArticleBuilder $builder, IFormField $field) use ($languageID) {
                $builder->getArticleContentBuilder($languageID)->setMetaTitle((string)$field->getSaveValue());
            })
            ->loadValueCallback(function (Article $object, IFormField $field) use ($languageID) {
                $field->value($this->getArticleContent($object, $languageID)?->metaTitle);
            });
        $fields[] = MultilineTextFormField::create("metaDescription{$suffix}")
            ->label('wcf.acp.article.metaDescription')
            ->saveValueCallback(function (ArticleBuilder $builder, IFormField $field) use ($languageID) {
                $builder->getArticleContentBuilder($languageID)->setMetaDescription((string)$field->getSaveValue());
            })
            ->loadValueCallback(function (Article $object, IFormField $field) use ($languageID) {
                $field->value($this->getArticleContent($object, $languageID)?->metaDescription);
            });

        return $fields;
    }

    /**
     * Creates and configures the WYSIWYG container for the given language. Pass
     * `null` for the monolingual content.
     */
    protected function createContentContainer(?Language $language): WysiwygFormContainer
    {
        $languageID = $language?->languageID;
        $suffix = $language !== null ? "_{$language->languageCode}" : '';

        $container = WysiwygFormContainer::create("content{$suffix}")
            ->label('wcf.acp.article.content')
            ->messageObjectType('com.woltlab.wcf.article.content')
            ->attachmentData('com.woltlab.wcf.article.content', objectID: $this->getAttachmentObjectID($languageID))
            ->required();
        if ($language !== null) {
            $container->enablePreviewButton(false);
        }

        $container->getWysiwygField()
            ->saveValueCallback(function (ArticleBuilder $builder, WysiwygFormField $field) use ($languageID) {
                $builder->getArticleContentBuilder($languageID)->setHtmlInputProcessor($field->getHtmlInputProcessor());
            })
            ->loadValueCallback(function (Article $object, IFormField $field) use ($languageID) {
                $field->value($this->getArticleContent($object, $languageID)?->content);
            });
        $container->getAttachmentField()->saveValueCallback(
            function (ArticleBuilder $builder, WysiwygAttachmentFormField $field) use ($languageID) {
                $builder->getArticleContentBuilder($languageID)->setAttachmentHandler($field->getAttachmentHandler());
            }
        );

        return $container;
    }

    /**
     * Returns the article content for the given language or `null` if it does not
     * exist. Pass `null` for the monolingual content.
     */
    protected function getArticleContent(Article $object, ?int $languageID): ?ArticleContent
    {
        if ($languageID !== null) {
            return $object->getArticleContents()[$languageID] ?? null;
        }

        return $object->getArticleContent();
    }

    /**
     * Returns a validator ensuring that the article slug is properly formatted
     * and unique within the given language.
     */
    protected function getSlugValidator(?int $languageID): FormFieldValidator
    {
        return new FormFieldValidator('slug', function (IFormField $field) use ($languageID) {
            $slug = \mb_strtolower(StringUtil::trim((string)$field->getSaveValue()));
            if ($slug === '') {
                return;
            }

            if (\preg_match('~^[a-z0-9\-_]+$~', $slug) === 0) {
                $field->addValidationError(new FormFieldValidationError(
                    'invalid',
                    'wcf.acp.article.slug.error.invalid'
                ));
                return;
            }

            $excludedArticleID = $this->formObject !== null ? $this->formObject->articleID : null;
            if (ArticleContent::findBySlug($slug, $languageID, $excludedArticleID) !== null) {
                $field->addValidationError(new FormFieldValidationError(
                    'notUnique',
                    'wcf.acp.article.slug.error.notUnique'
                ));
            }
        });
    }

    #[\Override]
    protected function getDatabaseObjectBuilder(): ArticleBuilder
    {
        $canManage = WCF::getSession()->hasPermission('admin.content.article.canManageArticle')
            || WCF::getSession()->hasPermission('admin.content.article.canManageOwnArticles');

        if ($this->formObject !== null) {
            $builder = ArticleBuilder::forUpdate($this->formObject);

            // The labels are saved without validating permissions and the label
            // form fields of label groups that the active user is not allowed to
            // set are unavailable, thus their existing labels have to be preserved
            // explicitly.
            $optionID = LabelHandler::getInstance()->getOptionID('canSetLabel');
            $labelIDs = [];
            $labels = ArticleLabelObjectHandler::getInstance()->getAssignedLabels(
                [$this->formObject->articleID],
                false
            )[$this->formObject->articleID] ?? [];
            foreach ($labels as $label) {
                $labelGroup = LabelHandler::getInstance()->getLabelGroup($label->groupID);
                if (
                    $labelGroup !== null
                    && $labelGroup->hasPermissions()
                    && !$labelGroup->getPermission($optionID)
                ) {
                    $labelIDs[] = $label->labelID;
                }
            }
            $builder->setLabelIDs($labelIDs);

            // Users without management permissions must not change the publication
            // status; the existing values are preserved by not setting them because
            // the corresponding form field is unavailable.

            return $builder;
        }

        $builder = ArticleBuilder::forCreate()
            ->setUser(WCF::getUser())
            ->setTime(\TIME_NOW)
            ->setIsMultilingual($this->isMultilingual === 1);

        if (!$canManage) {
            $builder
                ->setPublicationStatus(Article::UNPUBLISHED)
                ->setPublicationDate(0);
        }

        return $builder;
    }

    #[\Override]
    protected function getCommand(DatabaseObjectBuilder $builder): callable
    {
        if ($this->formObject !== null) {
            return new UpdateArticle($builder);
        }

        return new CreateArticle($builder);
    }

    protected function getAttachmentObjectID(?int $languageID = null): ?int
    {
        return null;
    }
}
