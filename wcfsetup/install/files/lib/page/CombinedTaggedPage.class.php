<?php

namespace wcf\page;

use Laminas\Diactoros\Response\RedirectResponse;
use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectList;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\tag\Tag;
use wcf\data\tag\TagList;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\request\LinkHandler;
use wcf\system\tagging\ICombinedTaggable;
use wcf\system\tagging\ITaggedListViewProvider;
use wcf\system\tagging\TypedTagCloud;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\StringUtil;

/**
 * Shows the a list of objects matching a combination of tags.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       5.2
 * @deprecated 6.2 Use `TaggedListViewPage` instead.
 *
 * @extends MultipleLinkPage<DatabaseObjectList<DatabaseObject>>
 */
class CombinedTaggedPage extends MultipleLinkPage
{
    /**
     * @var ObjectType[]
     */
    public $availableObjectTypes = [];

    /**
     * @inheritDoc
     */
    public $neededModules = ['MODULE_TAGGING'];

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['user.tag.canViewTag'];

    /**
     * @var ?ObjectType
     */
    public $objectType;

    /**
     * @var ICombinedTaggable<DatabaseObjectList<DatabaseObject>>
     */
    public $processor;

    /**
     * @var Tag[]
     */
    public $tags = [];

    /**
     * @var int[]
     */
    public $tagIDs = [];

    /**
     * @var TypedTagCloud
     */
    public $tagCloud;

    /**
     * @var int[]
     * @since 6.0
     */
    public $itemsPerType = [];

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (isset($_GET['tagIDs']) && \is_array($_GET['tagIDs'])) {
            $this->tagIDs = ArrayUtil::toIntegerArray($_GET['tagIDs']);
        }
        if (empty($this->tagIDs)) {
            throw new IllegalLinkException();
        } elseif (\count($this->tagIDs) > SEARCH_MAX_COMBINED_TAGS) {
            throw new PermissionDeniedException();
        }

        $tagList = new TagList();
        $tagList->getConditionBuilder()->add('tagID IN (?)', [$this->tagIDs]);
        $tagList->readObjects();

        $this->tags = $tagList->getObjects();
        if (empty($this->tags)) {
            throw new IllegalLinkException();
        }

        $this->availableObjectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.tagging.taggableObject');
        foreach ($this->availableObjectTypes as $key => $objectType) {
            if (!$objectType->validateOptions() || !$objectType->validatePermissions()) {
                unset($this->availableObjectTypes[$key]);
            }
        }

        if (empty($this->availableObjectTypes)) {
            throw new IllegalLinkException();
        }

        $this->readItemsPerType();

        if (isset($_REQUEST['objectType'])) {
            $objectType = StringUtil::trim($_REQUEST['objectType']);
            if (!isset($this->availableObjectTypes[$objectType])) {
                throw new IllegalLinkException();
            }
            $this->objectType = $this->availableObjectTypes[$objectType];
        } else {
            foreach ($this->availableObjectTypes as $key => $objectType) {
                if ($this->itemsPerType[$key]) {
                    $this->objectType = $objectType;
                    break;
                }
            }

            if (!$this->objectType) {
                throw new IllegalLinkException();
            }
        }

        if ($this->objectType->getProcessor() instanceof ITaggedListViewProvider) {
            return new RedirectResponse(
                LinkHandler::getInstance()->getControllerLink(TaggedListViewPage::class, [
                    'objectType' => $this->objectType->objectType,
                    'tagIDs' => $this->tagIDs,
                ]),
            );
        } else {
            $this->processor = $this->objectType->getProcessor();
        }
    }

    /**
     * @inheritDoc
     */
    protected function initObjectList()
    {
        $this->objectList = $this->processor->getObjectListFor($this->tags);
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        $this->tagCloud = new TypedTagCloud($this->objectType->objectType);
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'combinedTags' => $this->tags,
            'tags' => $this->tagCloud->getTags(100),
            'availableObjectTypes' => $this->availableObjectTypes,
            'objectType' => $this->objectType->objectType,
            'resultListTemplateName' => $this->processor->getTemplateName(),
            'resultListApplication' => $this->processor->getApplication(),
            'itemsPerType' => $this->itemsPerType,
            'objectTypeLinks' => $this->getObjectTypeLinks(),
        ]);

        if (\count($this->objectList) === 0) {
            @\header('HTTP/1.1 404 Not Found');
        }
    }

    /**
     * @return list<array{
     *  objectType: string,
     *  title: string,
     *  link: string,
     *  items: int,
     * }>
     */
    protected function getObjectTypeLinks(): array
    {
        $links = [];
        foreach ($this->availableObjectTypes as $objectType) {
            $items = $this->itemsPerType[$objectType->objectType] ?? 0;
            if ($items === 0) {
                continue;
            }

            $processor = $objectType->getProcessor();
            if ($processor instanceof ITaggedListViewProvider) {
                $title = $processor->getObjectTypeTitle();
                $controller = TaggedListViewPage::class;
            } else {
                $title = WCF::getLanguage()->getDynamicVariable('wcf.tagging.objectType.' . $objectType->objectType);
                $controller = CombinedTaggedPage::class;
            }

            $links[] = [
                'objectType' => $objectType->objectType,
                'title' => $title,
                'link' => LinkHandler::getInstance()->getControllerLink(
                    $controller,
                    [
                        'objectType' => $objectType->objectType,
                        'tagIDs' => $this->tagIDs
                    ]
                ),
                'items' => $items,
            ];
        }

        return $links;
    }

    protected function readItemsPerType(): void
    {
        foreach ($this->availableObjectTypes as $key => $objectType) {
            $processor = $objectType->getProcessor();
            if ($processor instanceof ITaggedListViewProvider) {
                $this->itemsPerType[$key] = $processor->getListView($this->tagIDs)->countItems();
            } else {
                $objectList = $processor->getObjectListFor($this->tags);
                \assert($objectList instanceof DatabaseObjectList);
                $this->itemsPerType[$key] = $objectList->countObjects();
            }
        }
    }
}
