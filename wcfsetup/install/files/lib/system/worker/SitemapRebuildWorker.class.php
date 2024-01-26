<?php

namespace wcf\system\worker;

use wcf\data\DatabaseObjectList;
use wcf\data\ILinkableObject;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\User;
use wcf\system\exception\ImplementationException;
use wcf\system\exception\ParentClassException;
use wcf\system\io\AtomicWriter;
use wcf\system\io\File;
use wcf\system\Regex;
use wcf\system\registry\RegistryHandler;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\FileUtil;
use wcf\util\MessageUtil;

/**
 * Worker implementation for rebuilding all sitemaps.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.1
 */
class SitemapRebuildWorker extends AbstractRebuildDataWorker
{
    /**
     * The limit of objects in one sitemap file.
     */
    public const SITEMAP_OBJECT_LIMIT = 25000;

    /**
     * Prefix for stored data in the registry.
     * @since 5.3
     */
    public const REGISTRY_PREFIX = 'sitemapData_';

    /**
     * @inheritDoc
     */
    public $limit = 250;

    /**
     * All object types for the site maps.
     * @var ObjectType[]
     */
    public $sitemapObjects = [];

    /**
     * The current worker data.
     * @var mixed[]
     */
    public $workerData = [];

    /**
     * The current temporary file as File object.
     * @var File
     */
    public $file;

    /**
     * The user profile of the actual user.
     * @var User
     */
    private $actualUser;

    /**
     * @var mixed[]
     */
    private $sitemapData = [];

    /**
     * @inheritDoc
     */
    public function initObjectList()
    {
        // This rebuild worker has no database object list
        // therefore we do nothing in this method an overwrite
        // the parent method, that it does not throw an exception.
    }

    /**
     * @inheritDoc
     */
    public function countObjects()
    {
        // changes session owner to 'System' during the building of sitemaps
        $this->changeUserToGuest();

        try {
            if ($this->count === null) {
                // reset count
                $this->count = 0;

                // read sitemaps
                $sitemapObjects = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.sitemap.object');
                foreach ($sitemapObjects as $sitemapObject) {
                    $this->prepareSitemapObject($sitemapObject);
                    $processor = $sitemapObject->getProcessor();

                    if (
                        $processor->isAvailableType()
                        && !$this->sitemapData[$sitemapObject->objectType]['isDisabled']
                    ) {
                        $this->sitemapObjects[] = $sitemapObject;

                        $list = $processor->getObjectList();

                        if (!($list instanceof DatabaseObjectList)) {
                            throw new ParentClassException(\get_class($list), DatabaseObjectList::class);
                        }

                        if (SITEMAP_INDEX_TIME_FRAME > 0 && $processor->getLastModifiedColumn() !== null) {
                            $list->getConditionBuilder()->add($processor->getLastModifiedColumn() . " > ?", [
                                TIME_NOW - SITEMAP_INDEX_TIME_FRAME * 86400, // one day (60 * 60 * 24)
                            ]);
                        }

                        $objectCount = $list->countObjects();
                        $iterations = \ceil($objectCount / $this->limit);
                        if (($objectCount % $this->limit) === 0) {
                            // We need an additional iteration to finalize the sitemap.
                            $iterations++;
                        }
                        $this->count += $iterations * $this->limit;
                    } else {
                        $this->deleteSitemaps($sitemapObject->objectType);
                    }
                }
            }
        } finally {
            // change session owner back to the actual user
            $this->changeToActualUser();
        }
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        // changes session owner to 'System' during the building of sitemaps
        $this->changeUserToGuest();

        try {
            $this->loadWorkerData();

            if (!isset($this->sitemapObjects[$this->workerData['sitemap']])) {
                $this->workerData['finished'] = true;
                $this->storeWorkerData();
            }

            // write sitemap index file if we have no active sitemap objects to prevent an outdated index file
            if (empty($this->sitemapObjects) && $this->loopCount == 0) {
                $this->writeIndexFile();
            }

            // check whether we should rebuild it
            if ((!isset($this->parameters['forceRebuild']) || !$this->parameters['forceRebuild']) && !$this->workerData['finished']) {
                $this->checkCache();
            }

            if ($this->workerData['finished']) {
                return;
            }

            $this->openFile();

            $sitemapObject = $this->sitemapObjects[$this->workerData['sitemap']]->getProcessor();
            $sitemapLoopCount = $this->workerData['sitemapLoopCount'];

            // delete all previously created sitemap files so that no more relics remain in the system
            if ($sitemapLoopCount === 0) {
                $this->deleteSitemaps($this->sitemapObjects[$this->workerData['sitemap']]->objectType);
            }

            /** @var DatabaseObjectList $objectList */
            $objectList = $sitemapObject->getObjectList();

            if (SITEMAP_INDEX_TIME_FRAME > 0 && $sitemapObject->getLastModifiedColumn() !== null) {
                $objectList->getConditionBuilder()->add($sitemapObject->getLastModifiedColumn() . " > ?", [
                    TIME_NOW - SITEMAP_INDEX_TIME_FRAME * 86400, // one day (60 * 60 * 24)
                ]);
            }

            $objectList->sqlLimit = $this->limit;
            $objectList->sqlOffset = $this->limit * $sitemapLoopCount;
            $objectList->readObjects();

            foreach ($objectList->getObjects() as $object) {
                if (!($object instanceof ILinkableObject)) {
                    throw new ImplementationException(\get_class($object), ILinkableObject::class);
                }

                $link = $object->getLink();
                $lastModifiedTime = ($sitemapObject->getLastModifiedColumn() === null) ? null : \date(
                    'c',
                    $object->{$sitemapObject->getLastModifiedColumn()}
                );

                $objectType = $this->sitemapObjects[$this->workerData['sitemap']];
                if ($sitemapObject->canView($object)) {
                    $this->file->write(WCF::getTPL()->fetch('sitemapEntry', 'wcf', [
                        // strip session links
                        'link' => MessageUtil::stripCrap($link),
                        'lastModifiedTime' => $lastModifiedTime,
                        'priority' => $objectType->priority,
                        'changeFreq' => $this->sitemapData[$objectType->objectType]['changeFreq'],
                    ]));

                    $this->workerData['dataCount']++;
                }
            }

            if ($this->workerData['dataCount'] + $this->limit > self::SITEMAP_OBJECT_LIMIT) {
                $packageID = $this->sitemapObjects[$this->workerData['sitemap']]->packageID;
                $objectTypeName = $this->sitemapObjects[$this->workerData['sitemap']]->objectType;
                $filename = $objectTypeName . '_' . $this->workerData['sitemapLoopCount'] . '.xml';
                $this->finishSitemap($filename, $packageID);

                $this->generateTmpFile(false);

                $this->workerData['dataCount'] = 0;
            }

            $closeFile = true;
            // finish sitemap
            if (\count($objectList) < $this->limit) {
                if ($this->workerData['dataCount'] > 0) {
                    $packageID = $this->sitemapObjects[$this->workerData['sitemap']]->packageID;
                    $filename = $this->sitemapObjects[$this->workerData['sitemap']]->objectType . '.xml';
                    $this->finishSitemap($filename, $packageID);
                    $this->generateTmpFile(false);
                }

                // increment data
                $this->workerData['dataCount'] = 0;
                $this->workerData['sitemapLoopCount'] = -1;
                $this->workerData['sitemap']++;

                if (\count($this->sitemapObjects) <= $this->workerData['sitemap']) {
                    $this->writeIndexFile();
                    $closeFile = false;
                }
            }

            $this->workerData['sitemapLoopCount']++;
            $this->storeWorkerData();
            if ($closeFile) {
                $this->closeFile();
            }
        } finally {
            // change session owner back to the actual user
            $this->changeToActualUser();
        }
    }

    /**
     * Checks if the sitemap has to be rebuilt. If not, this method marks the sitemap as built.
     */
    protected function checkCache()
    {
        $object = (isset($this->sitemapObjects[$this->workerData['sitemap']])) ? $this->sitemapObjects[$this->workerData['sitemap']] : false;
        while (
            $object
            && \file_exists(self::getSitemapPath() . $object->objectType . '.xml')
            && \filectime(self::getSitemapPath() . $object->objectType . '.xml') > TIME_NOW - ($this->sitemapData[$object->objectType]['rebuildTime'] ?: 60 * 60 * 24 * 7)
        ) {
            $filenames = \array_merge(
                \glob(self::getSitemapPath() . $object->objectType . '_*'),
                [self::getSitemapPath() . $object->objectType . '.xml']
            );
            foreach ($filenames as $filename) {
                $this->workerData['sitemaps'][] = self::getSitemapURL() . \basename($filename);
            }

            $this->workerData['sitemap']++;

            if (!isset($this->sitemapObjects[$this->workerData['sitemap']])) {
                $this->writeIndexFile(false);

                // if we don't have to refresh any data, we set loopCount to one
                // so that we no init a new $workerData session
                if ($this->loopCount == 0) {
                    $this->loopCount = 1;
                }
                $this->storeWorkerData();
                break;
            } else {
                $object = $this->sitemapObjects[$this->workerData['sitemap']];
            }
        }
    }

    /**
     * Writes the sitemap.xml index file and links all sitemaps.
     *
     * @param bool $closeFile Close a previously opened handle.
     */
    protected function writeIndexFile($closeFile = true)
    {
        $file = new AtomicWriter(self::getSitemapPath() . 'sitemap.xml');
        $file->write(WCF::getTPL()->fetch('sitemapIndex', 'wcf', [
            'sitemaps' => $this->workerData['sitemaps'],
        ]));
        $file->flush();
        $file->close();

        $this->workerData['finished'] = true;

        if ($closeFile) {
            $this->closeFile();
        }

        if ($this->workerData['tmpFile'] && \file_exists($this->workerData['tmpFile'])) {
            \unlink($this->workerData['tmpFile']);
        }

        $this->registerSitemapFiles();
    }

    /**
     * Generates a new temporary file and appends the sitemap start.
     *
     * @param bool $closeFile Close a previously opened handle.
     */
    protected function generateTmpFile($closeFile = true)
    {
        if ($closeFile) {
            $this->closeFile();
        }

        $this->workerData['tmpFile'] = FileUtil::getTemporaryFilename('sitemap_' . $this->workerData['sitemap'] . '_');

        $this->openFile();

        $this->file->write(WCF::getTPL()->fetch('shared_sitemapStart'));
    }

    /**
     * Open the current temporary file.
     */
    protected function openFile()
    {
        if (!\file_exists($this->workerData['tmpFile'])) {
            \touch($this->workerData['tmpFile']);
        }

        $this->file = new File($this->workerData['tmpFile'], 'ab');
    }

    /**
     * Closes the current temporary file, iff a File is opened.
     */
    protected function closeFile()
    {
        if ($this->file instanceof File) {
            $this->file->close();
        }
    }

    /**
     * Writes the current temporary file in a finished sitemap file. The param
     * $filename defines the sitemap filename.
     *
     * @param string $filename
     * @param int $packageID
     */
    protected function finishSitemap($filename, $packageID)
    {
        $this->file->write(WCF::getTPL()->fetch('shared_sitemapEnd'));
        $this->file->close();

        \rename($this->workerData['tmpFile'], self::getSitemapPath() . $filename);

        // add sitemap to the successfully built sitemaps
        $this->workerData['sitemaps'][] = self::getSitemapURL() . $filename;

        // Register sitemap for the package installation file log.
        if (!isset($this->workerData['filesToPackage'][$packageID])) {
            $this->workerData['filesToPackage'][$packageID] = [];
        }
        $this->workerData['filesToPackage'][$packageID][] = 'sitemaps/' . $filename;
    }

    private function registerSitemapFiles()
    {
        $sql = "INSERT IGNORE INTO  wcf" . WCF_N . "_package_installation_file_log
                                    (packageID, filename, application)
                VALUES              (?, ?, ?)";
        $statement = WCF::getDB()->prepareStatement($sql);

        WCF::getDB()->beginTransaction();
        foreach ($this->workerData['filesToPackage'] as $packageID => $files) {
            foreach ($files as $file) {
                $statement->execute([
                    $packageID,
                    $file,
                    'wcf',
                ]);
            }
        }
        WCF::getDB()->commitTransaction();
    }

    /**
     * Stores the current worker data in a session.
     */
    protected function storeWorkerData()
    {
        WCF::getSession()->register('sitemapRebuildWorkerData', $this->workerData);
    }

    /**
     * Load the current worker data and set the default values, if isn't any data stored.
     */
    protected function loadWorkerData()
    {
        $this->workerData = WCF::getSession()->getVar('sitemapRebuildWorkerData');

        if ($this->loopCount == 0) {
            $this->workerData = [
                'sitemap' => 0,
                'sitemapLoopCount' => 0,
                'dataCount' => 0,
                'tmpFile' => '',
                'sitemaps' => [],
                'finished' => false,
                'filesToPackage' => [],
            ];

            $this->generateTmpFile();
        }
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        WCF::getSession()->checkPermissions(['admin.management.canRebuildData']);
    }

    /**
     * @inheritDoc
     */
    public function getProceedURL()
    {
        return LinkHandler::getInstance()->getLink('SitemapList', [
            'isACP' => true,
        ]);
    }

    /**
     * Returns the relative sitemap folder path.
     *
     * @return  string
     */
    public static function getSitemapPath()
    {
        return WCF_DIR . 'sitemaps/';
    }

    /**
     * Returns the full sitemap folder path.
     *
     * @return  string
     */
    public static function getSitemapURL()
    {
        return WCF::getPath() . 'sitemaps/';
    }

    /**
     * Unlink the sitemap files for a given object type name.
     *
     * @param string $objectTypeName
     */
    private function deleteSitemaps($objectTypeName)
    {
        $files = @\glob(self::getSitemapPath() . $objectTypeName . '*.xml');
        if (\is_array($files)) {
            $regex = new Regex(\preg_quote($objectTypeName) . '(_[0-9]*|).xml');
            foreach ($files as $filename) {
                if ($regex->match(\basename($filename))) {
                    \unlink($filename);
                }
            }
        }
    }

    /**
     * Saves the actual user and changes the session owner to a guest.
     */
    private function changeUserToGuest()
    {
        $this->actualUser = WCF::getUser();

        // login as system user
        WCF::getSession()->changeUser(new User(null, ['username' => 'System', 'userID' => 0]), true);
    }

    /**
     * Changes the session back to the actual user.
     */
    private function changeToActualUser()
    {
        WCF::getSession()->changeUser($this->actualUser, true);
    }

    /**
     * Reads the columns changed by the user for this sitemap object from the registry.
     *
     * @param ObjectType $object
     * @since       5.3
     */
    private function prepareSitemapObject(ObjectType $object): void
    {
        $this->sitemapData[$object->objectType] = [
            'changeFreq' => $object->changeFreq,
            'rebuildTime' => $object->rebuildTime,
            'isDisabled' => 0,
        ];

        $sitemapData = RegistryHandler::getInstance()->get(
            'com.woltlab.wcf',
            self::REGISTRY_PREFIX . $object->objectType
        );

        if ($sitemapData !== null) {
            $sitemapData = @\unserialize($sitemapData);

            if (\is_array($sitemapData)) {
                $this->sitemapData[$object->objectType]['changeFreq'] = $sitemapData['changeFreq'];
                $this->sitemapData[$object->objectType]['rebuildTime'] = $sitemapData['rebuildTime'];
                $this->sitemapData[$object->objectType]['isDisabled'] = $sitemapData['isDisabled'];
            }
        }
    }
}
