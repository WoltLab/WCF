<?php

namespace wcf\system\worker;

use wcf\data\object\type\ObjectTypeCache;
use wcf\system\exception\SystemException;
use wcf\system\exporter\IExporter;
use wcf\system\importer\ImportHandler;
use wcf\system\WCF;

/**
 * Worker implementation for data import.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class ImportWorker extends AbstractWorker
{
    /**
     * import data
     * @var array
     */
    protected $importData;

    /**
     * exporter object
     * @var \wcf\system\exporter\IExporter
     */
    protected $exporter;

    /**
     * @inheritDoc
     */
    public function validate()
    {
        WCF::getSession()->checkPermissions(['admin.management.canImportData']);

        if (!isset($this->parameters['objectType'])) {
            throw new SystemException("parameter 'objectType' missing");
        }

        // get import data
        $this->importData = WCF::getSession()->getVar('importData');
        if ($this->importData === null) {
            throw new SystemException("import data missing");
        }
    }

    /**
     * Initializes the exporter.
     */
    protected function bootstrap(): void
    {
        if ($this->exporter) {
            throw new \BadMethodCallException('The exporter is already bootstrapped.');
        }

        $this->exporter = ObjectTypeCache::getInstance()
            ->getObjectTypeByName('com.woltlab.wcf.exporter', $this->importData['exporterName'])
            ->getProcessor();

        $this->exporter->setData(
            $this->importData['dbHost'],
            $this->importData['dbUser'],
            $this->importData['dbPassword'],
            $this->importData['dbName'],
            $this->importData['dbPrefix'],
            $this->importData['fileSystemPath'],
            $this->importData['additionalData']
        );
        $this->exporter->init();

        ImportHandler::getInstance()->setUserMergeMode($this->importData['userMergeMode']);

        ImportHandler::getInstance()->setImportHash(\substr(
            \sha1(
                $this->importData['dbHost'] . $this->importData['dbName'] . $this->importData['dbPrefix']
            ),
            0,
            8
        ));
    }

    /**
     * Returns the exporter instance.
     */
    protected function getExporter(): IExporter
    {
        if (!$this->exporter) {
            $this->bootstrap();
            \assert($this->exporter);
        }

        return $this->exporter;
    }

    /**
     * @inheritDoc
     */
    protected function countObjects()
    {
        $this->count = $this->getExporter()->countLoops($this->parameters['objectType']);
    }

    /**
     * @inheritDoc
     */
    public function getProgress()
    {
        $this->countObjects();

        if (!$this->count) {
            return 100;
        }

        $progress = (($this->loopCount + 1) / $this->count) * 100;
        if ($progress > 100) {
            $progress = 100;
        }

        return \floor($progress);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        if (!$this->count) {
            return;
        }

        // disable mysql strict mode
        $sql = "SET SESSION sql_mode = 'ANSI,ONLY_FULL_GROUP_BY'";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute();

        $this->getExporter()->exportData($this->parameters['objectType'], $this->loopCount);
    }

    /**
     * @inheritDoc
     */
    public function getProceedURL()
    {
        return '';
    }
}
