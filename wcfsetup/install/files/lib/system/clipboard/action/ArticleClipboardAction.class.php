<?php

namespace wcf\system\clipboard\action;

use wcf\data\article\Article;
use wcf\data\article\ArticleAction;
use wcf\data\category\CategoryNodeTree;
use wcf\data\clipboard\action\ClipboardAction;
use wcf\system\WCF;

/**
 * Clipboard action implementation for articles.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Clipboard\Action
 * @since   3.1
 */
class ArticleClipboardAction extends AbstractClipboardAction
{
    /**
     * @inheritDoc
     */
    protected $actionClassActions = [
        'delete',
        'publish',
        'restore',
        'trash',
        'unpublish',
    ];

    /**
     * @inheritDoc
     */
    protected $supportedActions = [
        'delete',
        'publish',
        'restore',
        'setCategory',
        'trash',
        'unpublish',
    ];

    /**
     * @inheritDoc
     */
    public function execute(array $objects, ClipboardAction $action)
    {
        $item = parent::execute($objects, $action);

        if ($item === null) {
            return;
        }

        // handle actions
        switch ($action->actionName) {
            case 'delete':
                $item->addInternalData(
                    'confirmMessage',
                    WCF::getLanguage()->getDynamicVariable(
                        'wcf.clipboard.item.com.woltlab.wcf.article.delete.confirmMessage',
                        [
                            'count' => $item->getCount(),
                        ]
                    )
                );
                break;

            case 'setCategory':
                $item->addInternalData('template', WCF::getTPL()->fetch('articleCategoryDialog', 'wcf', [
                    'categoryNodeList' => (new CategoryNodeTree('com.woltlab.wcf.article.category'))->getIterator(),
                ]));
                break;

            case 'trash':
                $item->addInternalData(
                    'confirmMessage',
                    WCF::getLanguage()->getDynamicVariable(
                        'wcf.clipboard.item.com.woltlab.wcf.article.trash.confirmMessage',
                        [
                            'count' => $item->getCount(),
                        ]
                    )
                );
                break;
        }

        return $item;
    }

    /**
     * @inheritDoc
     */
    public function getClassName()
    {
        return ArticleAction::class;
    }

    /**
     * @inheritDoc
     */
    public function getTypeName()
    {
        return 'com.woltlab.wcf.article';
    }

    /**
     * Returns the ids of the articles that can be deleted.
     *
     * @return  int[]
     */
    public function validateDelete()
    {
        $objectIDs = [];

        /** @var Article $article */
        foreach ($this->objects as $article) {
            if ($article->canDelete() && $article->isDeleted) {
                $objectIDs[] = $article->articleID;
            }
        }

        return $objectIDs;
    }

    /**
     * Returns the ids of the articles that can be published.
     *
     * @return  int[]
     */
    public function validatePublish()
    {
        $objectIDs = [];

        /** @var Article $article */
        foreach ($this->objects as $article) {
            if ($article->canPublish() && $article->publicationStatus == Article::UNPUBLISHED) {
                $objectIDs[] = $article->articleID;
            }
        }

        return $objectIDs;
    }

    /**
     * Returns the ids of the articles that can be restored.
     *
     * @return  int[]
     */
    public function validateRestore()
    {
        return $this->validateDelete();
    }

    /**
     * Returns the ids of the articles whose category can be set.
     *
     * @return  int[]
     */
    public function validateSetCategory()
    {
        if (!WCF::getSession()->getPermission('admin.content.article.canManageArticle')) {
            return [];
        }

        return \array_keys($this->objects);
    }

    /**
     * Returns the ids of the articles that can be trashed.
     *
     * @return  int[]
     */
    public function validateTrash()
    {
        $objectIDs = [];

        /** @var Article $article */
        foreach ($this->objects as $article) {
            if ($article->canDelete() && !$article->isDeleted) {
                $objectIDs[] = $article->articleID;
            }
        }

        return $objectIDs;
    }

    /**
     * Returns the ids of the articles that can be unpublished.
     *
     * @return  int[]
     */
    public function validateUnpublish()
    {
        $objectIDs = [];

        /** @var Article $article */
        foreach ($this->objects as $article) {
            if ($article->canPublish() && $article->publicationStatus == Article::PUBLISHED) {
                $objectIDs[] = $article->articleID;
            }
        }

        return $objectIDs;
    }
}
