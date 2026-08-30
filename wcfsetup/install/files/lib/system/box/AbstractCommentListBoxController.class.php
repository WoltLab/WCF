<?php

namespace wcf\system\box;

use wcf\data\comment\CommentList;
use wcf\data\comment\ViewableCommentList;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\ignore\UserIgnore;
use wcf\system\exception\InvalidObjectTypeException;
use wcf\system\user\UserProfileHandler;
use wcf\system\WCF;

/**
 * Abstract box controller implementation for a list of comments for a certain type of objects.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectListBoxController<CommentList>
 */
abstract class AbstractCommentListBoxController extends AbstractDatabaseObjectListBoxController
{
    /**
     * @inheritDoc
     */
    public $defaultLimit = 5;

    /**
     * name of the commentable object type the listed comments belong to
     * @var string
     */
    protected $objectTypeName = '';

    /**
     * commentable object type the listed comments belong to
     * @var ObjectType
     */
    public $objectType;

    /**
     * @inheritDoc
     */
    protected $sortFieldLanguageItemPrefix = 'wcf.comment.sortField';

    /**
     * @inheritDoc
     */
    protected static $supportedPositions = [
        'sidebarLeft',
        'sidebarRight',
    ];

    /**
     * @inheritDoc
     */
    public $validSortFields = ['time'];

    public function __construct()
    {
        $this->objectType = ObjectTypeCache::getInstance()->getObjectTypeByName(
            'com.woltlab.wcf.comment.commentableContent',
            $this->objectTypeName
        );
        if ($this->objectType === null) {
            throw new InvalidObjectTypeException($this->objectTypeName, 'com.woltlab.wcf.comment.commentableContent');
        }

        if ($this->validSortFields !== [] && \MODULE_LIKE !== 0) {
            $this->validSortFields[] = 'cumulativeLikes';
        }

        parent::__construct();
    }

    /**
     * Applies object type-specific filters to the comments.
     *
     * @since 6.3
     */
    protected function applyFilters(CommentList $commentList): void
    {
        // does nothing by default
    }

    /**
     * @deprecated 6.3 Override `applyFilters()` instead.
     * @return void
     */
    protected function applyObjectTypeFilters(ViewableCommentList $commentList)
    {
        // does nothing by default
    }

    #[\Override]
    protected function getObjectList(): CommentList
    {
        $commentList = $this->createCommentList();
        $commentList->getConditionBuilder()->add('comment.isDisabled = ?', [0]);
        $commentList->getConditionBuilder()->add('comment.objectTypeID = ?', [$this->objectType->objectTypeID]);

        if (UserProfileHandler::getInstance()->getIgnoredUsers(UserIgnore::TYPE_HIDE_MESSAGES) !== []) {
            $commentList->getConditionBuilder()->add(
                "(comment.userID IS NULL OR comment.userID NOT IN (?))",
                [UserProfileHandler::getInstance()->getIgnoredUsers(UserIgnore::TYPE_HIDE_MESSAGES)]
            );
        }

        return $commentList;
    }

    #[\Override]
    protected function getTemplate()
    {
        return WCF::getTPL()->render('wcf', 'boxSidebarCommentList', [
            'boxCommentList' => $this->objectList,
            'boxSortField' => $this->sortField,
        ]);
    }

    private function createCommentList(): CommentList
    {
        if ($this->usesLegacyFilters()) {
            $commentList = new ViewableCommentList();
            $this->applyObjectTypeFilters($commentList);

            return $commentList;
        }

        $commentList = new CommentList();
        $this->applyFilters($commentList);

        return $commentList;
    }

    private function usesLegacyFilters(): bool
    {
        $method = new \ReflectionMethod($this, 'applyObjectTypeFilters');

        return $method->getDeclaringClass()->getName() !== self::class;
    }
}
