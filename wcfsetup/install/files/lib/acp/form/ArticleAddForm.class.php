<?php

namespace wcf\acp\form;

use wcf\command\article\MarkArticleAsRead;
use wcf\data\article\Article;
use wcf\data\article\ArticleAction;
use wcf\data\article\category\ArticleCategory;
use wcf\data\category\CategoryNodeTree;
use wcf\data\user\User;
use wcf\form\AbstractFormBuilderForm;
use wcf\system\cache\builder\ArticleCategoryLabelCacheBuilder;
use wcf\system\exception\NamedUserException;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\container\TabFormContainer;
use wcf\system\form\builder\container\TabMenuFormContainer;
use wcf\system\form\builder\container\wysiwyg\WysiwygFormContainer;
use wcf\system\form\builder\data\processor\CustomFormDataProcessor;
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
use wcf\system\form\builder\IFormDocument;
use wcf\system\label\LabelHandler;
use wcf\system\label\object\ArticleLabelObjectHandler;
use wcf\system\language\LanguageFactory;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;
use wcf\util\HtmlString;

/**
 * Shows the article add form.
 *
 * @author      Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractFormBuilderForm<Article>
 */
class ArticleAddForm extends AbstractFormBuilderForm
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
    public $objectActionClass = ArticleAction::class;

    /**
     * @inheritDoc
     */
    public $objectEditLinkController = ArticleEditForm::class;

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
            if ($this->categoryID) {
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
                ->value($this->isMultilingual),
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
                        })),
                    ...$labelFormFields,
                    UserFormField::create('userID')
                        ->label('wcf.acp.article.author')
                        ->required()
                        ->value(WCF::getUser()->userID),
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
                        })),
                    RadioButtonFormField::create('publicationStatus')
                        ->label('wcf.acp.article.publicationStatus')
                        ->options([
                            Article::PUBLISHED => 'wcf.acp.article.publicationStatus.published',
                            Article::UNPUBLISHED => 'wcf.acp.article.publicationStatus.unpublished',
                            Article::DELAYED_PUBLICATION => 'wcf.acp.article.publicationStatus.delayed',
                        ])
                        ->available($canManage)
                        ->value(Article::PUBLISHED)
                        ->required(),
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
                        })),
                    BooleanFormField::create('enableComments')
                        ->label('wcf.acp.article.enableComments')
                        ->value(\ARTICLE_ENABLE_COMMENTS_DEFAULT_VALUE),
                ]),
        ]);

        if ($this->isMultilingual) {
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
                ->labelGroup($labelGroup);
        }

        return $labelFormFields;
    }

    protected function createMonolingualForm(): void
    {
        $contentFields = [];
        if (WCF::getSession()->hasPermission('admin.content.cms.canUseMedia')) {
            $contentFields[] = SingleMediaSelectionFormField::create('imageID')
                ->label('wcf.acp.article.image')
                ->imageOnly();
            $contentFields[] = SingleMediaSelectionFormField::create('teaserImageID')
                ->label('wcf.acp.article.teaserImage')
                ->imageOnly();
        }

        $contentFields = \array_merge($contentFields, [
            TitleFormField::create('title')
                ->required()
                ->maximumLength(255),
            MultilineTextFormField::create('teaser')
                ->label('wcf.acp.article.teaser'),
            TagFormField::create('tags')
                ->available(\MODULE_TAGGING !== 0)
                ->objectType('com.woltlab.wcf.article'),
            TextFormField::create('metaTitle')
                ->label('wcf.acp.article.metaTitle')
                ->maximumLength(255),
            MultilineTextFormField::create('metaDescription')
                ->label('wcf.acp.article.metaDescription'),
        ]);

        $this->form->appendChildren([
            FormContainer::create('contentSection')
                ->label('wcf.acp.article.content')
                ->appendChildren($contentFields),
            WysiwygFormContainer::create('content')
                ->label('wcf.acp.article.content')
                ->messageObjectType('com.woltlab.wcf.article.content')
                ->attachmentData('com.woltlab.wcf.article.content', objectID: $this->getAttachmentObjectID())
                ->required(),
        ]);
    }

    protected function createMultilingualForm(): void
    {
        $tabContainer = TabMenuFormContainer::create('languages');
        $this->form->appendChild($tabContainer);

        foreach (LanguageFactory::getInstance()->getLanguages() as $language) {
            $lc = $language->languageCode;

            $contentFields = [];
            if (WCF::getSession()->hasPermission('admin.content.cms.canUseMedia')) {
                $contentFields[] = SingleMediaSelectionFormField::create("imageID_{$lc}")
                    ->label('wcf.acp.article.image')
                    ->imageOnly();
                $contentFields[] = SingleMediaSelectionFormField::create("teaserImageID_{$lc}")
                    ->label('wcf.acp.article.teaserImage')
                    ->imageOnly();
            }

            $contentFields = \array_merge($contentFields, [
                TitleFormField::create("title_{$lc}")
                    ->required()
                    ->maximumLength(255),
                MultilineTextFormField::create("teaser_{$lc}")
                    ->label('wcf.acp.article.teaser'),
                TagFormField::create("tags_{$lc}")
                    ->available(\MODULE_TAGGING !== 0)
                    ->objectType('com.woltlab.wcf.article'),
                TextFormField::create("metaTitle_{$lc}")
                    ->label('wcf.acp.article.metaTitle')
                    ->maximumLength(255),
                MultilineTextFormField::create("metaDescription_{$lc}")
                    ->label('wcf.acp.article.metaDescription'),
            ]);

            $tabContainer->appendChild(
                TabFormContainer::create("language_{$lc}")
                    ->label($language->languageName)
                    ->appendChildren([
                        FormContainer::create("contentSection_{$lc}")
                            ->appendChildren($contentFields),
                        WysiwygFormContainer::create("content_{$lc}")
                            ->label('wcf.acp.article.content')
                            ->messageObjectType('com.woltlab.wcf.article.content')
                            ->attachmentData(
                                'com.woltlab.wcf.article.content',
                                objectID: $this->getAttachmentObjectID($language->languageID)
                            )
                            ->required()
                            ->enablePreviewButton(false),
                    ])
            );
        }
    }

    #[\Override]
    public function finalizeForm(): void
    {
        parent::finalizeForm();

        $this->form->getDataHandler()
            ->addProcessor(
                new CustomFormDataProcessor(
                    'authorProcessor',
                    function (IFormDocument $document, array $parameters) {
                        $user = new User($parameters['data']['userID']);
                        $parameters['data']['username'] = $user->username;

                        return $parameters;
                    }
                )
            )
            ->addProcessor(
                new CustomFormDataProcessor(
                    'publicationDateProcessor',
                    static function (IFormDocument $document, array $parameters) {
                        if (
                            !isset($parameters['data']['publicationDate'])
                            || $parameters['data']['publicationDate'] === ''
                        ) {
                            $parameters['data']['publicationDate'] = 0;
                        }

                        return $parameters;
                    }
                )
            )
            ->addProcessor(
                new CustomFormDataProcessor(
                    'contentProcessor',
                    function (IFormDocument $document, array $parameters) {
                        $parameters['content'] = [];

                        if ($this->isMultilingual) {
                            foreach (LanguageFactory::getInstance()->getLanguages() as $language) {
                                $lc = $language->languageCode;
                                $lid = $language->languageID;

                                $parameters['content'][$lid] = [
                                    'title' => $parameters['data']["title_{$lc}"] ?? '',
                                    'tags' => $parameters["tags_{$lc}"] ?? [],
                                    'teaser' => $parameters['data']["teaser_{$lc}"] ?? '',
                                    'content' => $parameters['data']["content_{$lc}"] ?? '',
                                    'htmlInputProcessor' => $parameters["content_{$lc}_htmlInputProcessor"] ?? null,
                                    'imageID' => $parameters['data']["imageID_{$lc}"] ?? null,
                                    'teaserImageID' => $parameters['data']["teaserImageID_{$lc}"] ?? null,
                                    'metaTitle' => $parameters['data']["metaTitle_{$lc}"] ?? '',
                                    'metaDescription' => $parameters['data']["metaDescription_{$lc}"] ?? '',
                                ];
                                if (isset($parameters["content_{$lc}_attachmentHandler"])) {
                                    $parameters['content'][$lid]['attachmentHandler'] = $parameters["content_{$lc}_attachmentHandler"];
                                }

                                unset(
                                    $parameters['data']["title_{$lc}"],
                                    $parameters['data']["teaser_{$lc}"],
                                    $parameters['data']["content_{$lc}"],
                                    $parameters['data']["imageID_{$lc}"],
                                    $parameters['data']["teaserImageID_{$lc}"],
                                    $parameters['data']["metaTitle_{$lc}"],
                                    $parameters['data']["metaDescription_{$lc}"],
                                    $parameters["tags_{$lc}"],
                                    $parameters["content_{$lc}_htmlInputProcessor"],
                                    $parameters["content_{$lc}_attachmentHandler"],
                                );
                            }
                        } else {
                            $parameters['content'][0] = [
                                'title' => $parameters['data']['title'] ?? '',
                                'tags' => $parameters['tags'] ?? [],
                                'teaser' => $parameters['data']['teaser'] ?? '',
                                'content' => $parameters['data']['content'] ?? '',
                                'htmlInputProcessor' => $parameters['content_htmlInputProcessor'] ?? null,
                                'imageID' => $parameters['data']['imageID'] ?? null,
                                'teaserImageID' => $parameters['data']['teaserImageID'] ?? null,
                                'metaTitle' => $parameters['data']['metaTitle'] ?? '',
                                'metaDescription' => $parameters['data']['metaDescription'] ?? '',
                            ];

                            if (isset($parameters['content_attachmentHandler'])) {
                                $parameters['content'][0]['attachmentHandler'] = $parameters['content_attachmentHandler'];
                            }

                            unset(
                                $parameters['data']['title'],
                                $parameters['data']['teaser'],
                                $parameters['data']['content'],
                                $parameters['data']['imageID'],
                                $parameters['data']['teaserImageID'],
                                $parameters['data']['metaTitle'],
                                $parameters['data']['metaDescription'],
                                $parameters['tags'],
                                $parameters['content_htmlInputProcessor'],
                                $parameters['content_attachmentHandler'],
                            );
                        }

                        return $parameters;
                    }
                )
            )
            ->addProcessor(
                new CustomFormDataProcessor(
                    'labelProcessor',
                    static function (IFormDocument $document, array $parameters) {
                        $parameters['labelIDs'] = $parameters['labelIDs'] ?? [];
                        $parameters['data']['hasLabels'] = $parameters['labelIDs'] !== [] ? 1 : 0;

                        return $parameters;
                    }
                )
            );
    }

    #[\Override]
    public function save(): void
    {
        if (
            !WCF::getSession()->hasPermission('admin.content.article.canManageArticle')
            && !WCF::getSession()->hasPermission('admin.content.article.canManageOwnArticles')
        ) {
            $this->additionalFields['publicationStatus'] = Article::UNPUBLISHED;
            $this->additionalFields['publicationDate'] = 0;
        }

        parent::save();

        /** @var Article $article */
        $article = $this->objectAction->getReturnValues()['returnValues'];

        // save labels
        $labelIDs = $this->objectAction->getParameters()['labelIDs'] ?? [];
        if (!empty($labelIDs)) {
            ArticleLabelObjectHandler::getInstance()->setLabels($labelIDs, $article->articleID);
        }

        // mark published article as read
        if ($article->publicationStatus == Article::PUBLISHED) {
            (new MarkArticleAsRead($article))();
        }
    }

    protected function getAttachmentObjectID(?int $languageID = null): ?int
    {
        return null;
    }
}
