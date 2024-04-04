<?php

namespace wcf\page;

use wcf\data\edit\history\entry\EditHistoryEntry;
use wcf\data\edit\history\entry\EditHistoryEntryList;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\edit\IHistorySavingObject;
use wcf\system\edit\IHistorySavingObjectTypeProvider;
use wcf\system\exception\IllegalLinkException;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\Diff;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;

/**
 * Compares two entries of the edit history.
 *
 * @author      Tim Duesterhus, Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class EditHistoryPage extends AbstractPage
{
    /**
     * @inheritDoc
     */
    public $neededModules = ['MODULE_EDIT_HISTORY'];

    /**
     * List of edit history entries.
     */
    public ?EditHistoryEntryList $objectList = null;

    /**
     * left / old version id
     */
    public int $oldID = 0;

    /**
     * left / old version
     */
    public ?EditHistoryEntry $old = null;

    /**
     * right / new version id
     */
    public int|string $newID = 0;

    /**
     * right / new version
     */
    public EditHistoryEntry|IHistorySavingObject|null $new = null;

    /**
     * differences between both versions
     */
    public ?array $diff = null;

    /**
     * object type of the requested object
     */
    public ?ObjectType $objectType = null;

    /**
     * id of the requested object
     */
    public int $objectID = 0;

    /**
     * requested object
     */
    public ?IHistorySavingObject $object = null;

    #[\Override]
    public function readParameters(): void
    {
        parent::readParameters();

        if (isset($_REQUEST['oldID'])) {
            $this->oldID = \intval($_REQUEST['oldID']);
            $this->old = new EditHistoryEntry($this->oldID);
            if (!$this->old->entryID) {
                throw new IllegalLinkException();
            }

            if (isset($_REQUEST['newID']) && $_REQUEST['newID'] !== 'current') {
                $this->newID = \intval($_REQUEST['newID']);
                $this->new = new EditHistoryEntry($this->newID);
                if (!$this->new->entryID) {
                    throw new IllegalLinkException();
                }
            }

            // if new version isn't 'current' check whether they are comparable
            if ($this->new) {
                // different objectTypes cannot be compared
                if ($this->old->objectTypeID != $this->new->objectTypeID) {
                    throw new IllegalLinkException();
                }
                // different items cannot be compared
                if ($this->old->objectID != $this->new->objectID) {
                    throw new IllegalLinkException();
                }
            }

            $this->objectID = $this->old->objectID;
            $this->objectType = ObjectTypeCache::getInstance()->getObjectType($this->old->objectTypeID);
        } elseif (isset($_REQUEST['objectID']) && isset($_REQUEST['objectType'])) {
            $this->objectID = \intval($_REQUEST['objectID']);
            $this->objectType = ObjectTypeCache::getInstance()->getObjectTypeByName(
                'com.woltlab.wcf.edit.historySavingObject',
                $_REQUEST['objectType']
            );
        } else {
            throw new IllegalLinkException();
        }

        if (!$this->objectType) {
            throw new IllegalLinkException();
        }

        /** @var IHistorySavingObjectTypeProvider $processor */
        $processor = $this->objectType->getProcessor();

        $this->object = $processor->getObjectByID($this->objectID);
        if (!$this->object->getObjectID()) {
            throw new IllegalLinkException();
        }
        $processor->checkPermissions($this->object);
        $this->object->setLocation();

        if (isset($_REQUEST['newID']) && !$this->new) {
            $this->new = $this->object;
            $this->newID = 'current';
        }

        if (!empty($_POST)) {
            HeaderUtil::redirect(LinkHandler::getInstance()->getLink('EditHistory', [
                'objectID' => $this->objectID,
                'objectType' => $this->objectType->objectType,
                'newID' => $this->newID,
                'oldID' => $this->oldID,
            ]));

            exit;
        }
    }

    #[\Override]
    public function readData(): void
    {
        parent::readData();

        $this->objectList = new EditHistoryEntryList();
        $this->objectList->sqlOrderBy = "time DESC, entryID DESC";
        $this->objectList->getConditionBuilder()->add('objectTypeID = ?', [$this->objectType->objectTypeID]);
        $this->objectList->getConditionBuilder()->add('objectID = ?', [$this->objectID]);
        $this->objectList->readObjects();

        $differ = Diff::getDefaultDiffer();

        // valid IDs were given, calculate diff
        if ($this->old && $this->new) {
            $a = \explode("\n", $this->prepareMessage($this->old->getMessage()));
            $b = \explode("\n", $this->prepareMessage($this->new->getMessage()));
            $this->diff = Diff::rawDiffFromSebastianDiff($differ->diffToArray($a, $b));

            // create word diff for small changes (only one consecutive paragraph modified)
            for ($i = 0, $max = \count($this->diff); $i < $max;) {
                $previousIsNotRemoved = !isset($this->diff[$i - 1][0]) || $this->diff[$i - 1][0] !== Diff::REMOVED;
                $currentIsRemoved = $this->diff[$i][0] === Diff::REMOVED;
                $nextIsAdded = isset($this->diff[$i + 1][0]) && $this->diff[$i + 1][0] === Diff::ADDED;
                $afterNextIsNotAdded = !isset($this->diff[$i + 2][0]) || $this->diff[$i + 2][0] !== Diff::ADDED;

                if ($previousIsNotRemoved && $currentIsRemoved && $nextIsAdded && $afterNextIsNotAdded) {
                    $a = \preg_split('/(\\W)/u', $this->diff[$i][1], -1, \PREG_SPLIT_DELIM_CAPTURE);
                    $b = \preg_split('/(\\W)/u', $this->diff[$i + 1][1], -1, \PREG_SPLIT_DELIM_CAPTURE);

                    $diff = Diff::rawDiffFromSebastianDiff($differ->diffToArray($a, $b));
                    $this->diff[$i][1] = '';
                    $this->diff[$i + 1][1] = '';
                    foreach ($diff as $entry) {
                        $entry[1] = StringUtil::encodeHTML($entry[1]);

                        if ($entry[0] === Diff::SAME) {
                            $this->diff[$i][1] .= $entry[1];
                            $this->diff[$i + 1][1] .= $entry[1];
                        } elseif ($entry[0] === Diff::REMOVED) {
                            $this->diff[$i][1] .= '<strong>' . $entry[1] . '</strong>';
                        } elseif ($entry[0] === Diff::ADDED) {
                            $this->diff[$i + 1][1] .= '<strong>' . $entry[1] . '</strong>';
                        }
                    }
                    $i += 2;
                } else {
                    $this->diff[$i][1] = StringUtil::encodeHTML($this->diff[$i][1]);
                    $i++;
                }
            }
        }

        // set default values
        if (!isset($_REQUEST['oldID']) && !isset($_REQUEST['newID'])) {
            foreach ($this->objectList as $object) {
                $this->oldID = $object->entryID;
                break;
            }
            $this->newID = 'current';
        }
    }

    private function prepareMessage(string $message): string
    {
        $message = $this->formatHtml($message);
        $message = StringUtil::trim($message);
        $message = StringUtil::unifyNewlines($message);

        return \preg_replace('/\n{2,}/', "\n", $message);
    }

    private function formatHtml(string $html): string
    {
        $bothTags = ['ol', 'pre', 'table', 'tr', 'ul', 'woltlab-quote', 'woltlab-spoiler'];
        $openingTag = \implode('|', ['br', ...$bothTags]);
        $closingTag = \implode('|', ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'li', 'td', 'th', ...$bothTags]);

        return \preg_replace("/(<(?:{$openingTag})>|<\\/(?:{$closingTag})>)/", "\\0\n", $html);
    }

    #[\Override]
    public function assignVariables(): void
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'oldID' => $this->oldID,
            'old' => $this->old,
            'newID' => $this->newID,
            'new' => $this->new,
            'object' => $this->object,
            'diff' => $this->diff,
            'objects' => $this->objectList,
            'objectID' => $this->objectID,
            'objectType' => $this->objectType,
        ]);
    }
}
