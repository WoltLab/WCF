<?php

namespace wcf\page;

use Laminas\Diactoros\Response\RedirectResponse;
use wcf\data\DatabaseObjectList;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\tag\Tag;
use wcf\data\tag\TagList;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\listView\AbstractListView;
use wcf\system\request\LinkHandler;
use wcf\system\tagging\ITaggedListViewProvider;
use wcf\system\tagging\TypedTagCloud;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\StringUtil;

/**
 * Shows the a list of tagged objects.
 *
 * @author      Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class TaggedListViewPage extends AbstractListViewPage
{
    /**
     * @inheritDoc
     */
    public $neededModules = ['MODULE_TAGGING'];

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['user.tag.canViewTag'];

    /**
     * @var ObjectType[]
     */
    public array $availableObjectTypes = [];

    /**
     * @var Tag[]
     */
    public array $tags = [];

    /**
     * @var int[]
     */
    public array $tagIDs = [];

    /**
     * @var int[]
     */
    public array $itemsPerType = [];

    public ITaggedListViewProvider $provider;
    public ObjectType $objectType;
    public TypedTagCloud $tagCloud;

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        if (isset($_GET['tagIDs']) && \is_array($_GET['tagIDs'])) {
            $this->tagIDs = ArrayUtil::toIntegerArray($_GET['tagIDs']);
        }
        if ($this->tagIDs === []) {
            throw new IllegalLinkException();
        } elseif (\count($this->tagIDs) > SEARCH_MAX_COMBINED_TAGS) {
            throw new PermissionDeniedException();
        }

        $tagList = new TagList();
        $tagList->getConditionBuilder()->add('tagID IN (?)', [$this->tagIDs]);
        $tagList->readObjects();

        $this->tags = $tagList->getObjects();
        if ($this->tags === []) {
            throw new IllegalLinkException();
        }

        $this->loadObjectTypes();
        if ($this->availableObjectTypes === []) {
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
            $this->provider = $this->objectType->getProcessor();
        } else {
            return new RedirectResponse(
                LinkHandler::getInstance()->getControllerLink(CombinedTaggedPage::class, [
                    'objectType' => $this->objectType->objectType,
                    'tagIDs' => $this->tagIDs,
                ]),
            );
        }
    }

    #[\Override]
    protected function createListView(): AbstractListView
    {
        return $this->provider->getListView($this->tagIDs);
    }

    #[\Override]
    protected function getBaseUrlParameters(): array
    {
        return [
            'objectType' => $this->objectType->objectType,
            'tagIDs' => $this->tagIDs,
        ];
    }

    #[\Override]
    public function readData()
    {
        parent::readData();

        $this->tagCloud = new TypedTagCloud($this->objectType->objectType);
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'combinedTags' => $this->tags,
            'tags' => $this->tagCloud->getTags(100),
            'availableObjectTypes' => $this->availableObjectTypes,
            'objectType' => $this->objectType->objectType,
            'itemsPerType' => $this->itemsPerType,
            'contentTitle' => $this->provider->getContentTitle(),
            'objectTypeLinks' => $this->getObjectTypeLinks(),
        ]);
    }

    protected function getObjectTypeLinks(): array
    {
        $links = [];
        foreach ($this->availableObjectTypes as $objectType) {
            if (empty($this->itemsPerType[$objectType->objectType])) {
                continue;
            }

            if ($objectType->getProcessor() instanceof ITaggedListViewProvider) {
                $processor = $objectType->getProcessor();
                \assert($processor instanceof ITaggedListViewProvider);

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
                'items' => $this->itemsPerType[$objectType->objectType],
            ];
        }

        return $links;
    }

    protected function readItemsPerType(): void
    {
        foreach ($this->availableObjectTypes as $key => $objectType) {
            if ($objectType->getProcessor() instanceof ITaggedListViewProvider) {
                $processor = $objectType->getProcessor();
                \assert($processor instanceof ITaggedListViewProvider);
                $this->itemsPerType[$key] = $processor->getListView($this->tagIDs)->countItems();
            } else {
                $objectList = $objectType->getProcessor()->getObjectListFor($this->tags);
                \assert($objectList instanceof DatabaseObjectList);
                $this->itemsPerType[$key] = $objectList->countObjects();
            }
        }
    }

    protected function loadObjectTypes(): void
    {
        $this->availableObjectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.tagging.taggableObject');
        foreach ($this->availableObjectTypes as $key => $objectType) {
            if (!$objectType->validateOptions() || !$objectType->validatePermissions()) {
                unset($this->availableObjectTypes[$key]);
            }
        }
    }
}
