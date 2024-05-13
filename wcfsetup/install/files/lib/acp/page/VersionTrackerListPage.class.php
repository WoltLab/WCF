<?php

namespace wcf\acp\page;

use wcf\data\IVersionTrackerObject;
use wcf\data\language\Language;
use wcf\page\AbstractPage;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\language\LanguageFactory;
use wcf\system\request\LinkHandler;
use wcf\system\version\IVersionTrackerProvider;
use wcf\system\version\VersionTracker;
use wcf\system\version\VersionTrackerEntry;
use wcf\system\WCF;
use wcf\util\Diff;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;

/**
 * Shows a list of tracked versions for provided object type and id.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class VersionTrackerListPage extends AbstractPage
{
    /**
     * object id
     * @var int
     */
    public $objectID = 0;

    /**
     * object type name
     * @var string
     */
    public $objectType = '';

    /**
     * @var IVersionTrackerProvider
     */
    public $objectTypeProcessor;

    /**
     * @var VersionTrackerEntry[]
     */
    public $versions = [];

    /**
     * left / old version id
     * @var int
     */
    public $oldID = 0;

    /**
     * left / old version
     * @var VersionTrackerEntry
     */
    public $old;

    /**
     * right / new version id
     * @var int
     */
    public $newID = 0;

    /**
     * right / new version
     * @var VersionTrackerEntry
     */
    public $new;

    /**
     * differences between both versions
     * @var array
     */
    public $diffs = [];

    /**
     * requested object
     * @var IVersionTrackerObject
     */
    public $object;

    /**
     * list of available languages for comparison
     * @var Language[]
     */
    public $languages = [];

    /**
     * property used for comparison
     * @var string
     */
    public $property = '';

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (isset($_REQUEST['objectID'])) {
            $this->objectID = \intval($_REQUEST['objectID']);
        }
        if (isset($_REQUEST['objectType'])) {
            $this->objectType = $_REQUEST['objectType'];
        }

        try {
            $objectType = VersionTracker::getInstance()->getObjectType($this->objectType);
        } catch (\InvalidArgumentException $e) {
            throw new IllegalLinkException();
        }

        $this->objectTypeProcessor = $objectType->getProcessor();
        if (!$this->objectTypeProcessor->canAccess()) {
            throw new PermissionDeniedException();
        }

        $this->activeMenuItem = $this->objectTypeProcessor->getActiveMenuItem();

        $this->object = $this->objectTypeProcessor->getObjectByID($this->objectID);
        if (!$this->object->getObjectID()) {
            throw new IllegalLinkException();
        }

        $this->versions = VersionTracker::getInstance()->getVersions($this->objectType, $this->objectID);

        if (isset($_REQUEST['oldID'])) {
            $this->oldID = \intval($_REQUEST['oldID']);
            $this->old = VersionTracker::getInstance()->getVersion($this->objectType, $this->oldID);
            if (!$this->old->versionID) {
                throw new IllegalLinkException();
            }

            if (isset($_REQUEST['newID']) && $_REQUEST['newID'] !== 'current') {
                $this->newID = \intval($_REQUEST['newID']);
                $this->new = VersionTracker::getInstance()->getVersion($this->objectType, $this->newID);
                if (!$this->new->versionID) {
                    throw new IllegalLinkException();
                }
            }
        }

        if (isset($_REQUEST['newID']) && !$this->new) {
            $this->new = $this->objectTypeProcessor->getCurrentVersion($this->object);
            $this->newID = 'current';
        }

        if (!empty($_POST)) {
            HeaderUtil::redirect(LinkHandler::getInstance()->getLink('VersionTrackerList', [
                'objectID' => $this->objectID,
                'objectType' => $this->objectType,
                'newID' => $this->newID,
                'oldID' => $this->oldID,
            ]));

            exit;
        }
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        $differ = Diff::getDefaultDiffer();

        // valid IDs were given, calculate diff
        if ($this->old && $this->new) {
            $languageIDs = $this->new->getLanguageIDs();

            if (\count($languageIDs) > 1 || $languageIDs[0] != 0) {
                foreach ($languageIDs as $i => $languageID) {
                    $language = LanguageFactory::getInstance()->getLanguage($languageID);
                    if ($language === null) {
                        unset($languageIDs[$i]);
                    } else {
                        $this->languages[$languageID] = $language;
                    }
                }

                $languageIDs = \array_unique($languageIDs);
            }

            $properties = $this->objectTypeProcessor->getTrackedProperties();
            foreach ($languageIDs as $languageID) {
                $this->diffs[$languageID] = [];

                foreach ($properties as $property) {
                    $a = \explode("\n", StringUtil::unifyNewlines($this->old->getPayload($property, $languageID)));
                    $b = \explode("\n", StringUtil::unifyNewlines($this->new->getPayload($property, $languageID)));
                    if ($a == $b) {
                        continue;
                    }

                    $rawDiff = Diff::rawDiffFromSebastianDiff($differ->diffToArray($a, $b));

                    // create word diff for small changes (only one consecutive paragraph modified)
                    for ($i = 0, $max = \count($rawDiff); $i < $max;) {
                        $previousIsNotRemoved = !isset($rawDiff[$i - 1][0]) || $rawDiff[$i - 1][0] !== Diff::REMOVED;
                        $currentIsRemoved = $rawDiff[$i][0] === Diff::REMOVED;
                        $nextIsAdded = isset($rawDiff[$i + 1][0]) && $rawDiff[$i + 1][0] === Diff::ADDED;
                        $afterNextIsNotAdded = !isset($rawDiff[$i + 2][0]) || $rawDiff[$i + 2][0] !== Diff::ADDED;

                        if ($previousIsNotRemoved && $currentIsRemoved && $nextIsAdded && $afterNextIsNotAdded) {
                            $a = \preg_split('/(\\W)/u', $rawDiff[$i][1], -1, \PREG_SPLIT_DELIM_CAPTURE);
                            $b = \preg_split('/(\\W)/u', $rawDiff[$i + 1][1], -1, \PREG_SPLIT_DELIM_CAPTURE);

                            $diff = Diff::rawDiffFromSebastianDiff($differ->diffToArray($a, $b));
                            $rawDiff[$i][1] = '';
                            $rawDiff[$i + 1][1] = '';
                            foreach ($diff as $entry) {
                                $entry[1] = StringUtil::encodeHTML($entry[1]);

                                if ($entry[0] === Diff::SAME) {
                                    $rawDiff[$i][1] .= $entry[1];
                                    $rawDiff[$i + 1][1] .= $entry[1];
                                } else {
                                    if ($entry[0] === Diff::REMOVED) {
                                        $rawDiff[$i][1] .= '<del>' . $entry[1] . '</del>';
                                    } else {
                                        if ($entry[0] === Diff::ADDED) {
                                            $rawDiff[$i + 1][1] .= '<ins>' . $entry[1] . '</ins>';
                                        }
                                    }
                                }
                            }
                            $i += 2;
                        } else {
                            $rawDiff[$i][1] = StringUtil::encodeHTML($rawDiff[$i][1]);
                            $i++;
                        }
                    }

                    $this->diffs[$languageID][$property] = $rawDiff;
                }
            }

            // simply template logic by treating diffs with only one language as "no i18n"
            if (\count($this->diffs) == 1 && !isset($this->diffs[0])) {
                $this->diffs = [\reset($this->diffs)];
            }
        }

        // set default values
        if (!isset($_REQUEST['oldID']) && !isset($_REQUEST['newID'])) {
            foreach ($this->versions as $version) {
                $this->oldID = $version->versionID;
                break;
            }
            $this->newID = 'current';
        }
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'oldID' => $this->oldID,
            'old' => $this->old,
            'newID' => $this->newID,
            'new' => $this->new,
            'diffs' => $this->diffs,
            'objectID' => $this->objectID,
            'objectType' => $this->objectType,
            'objectTypeProcessor' => $this->objectTypeProcessor,
            'object' => $this->object,
            'languages' => $this->languages,
            'versions' => $this->versions,
        ]);
    }
}
