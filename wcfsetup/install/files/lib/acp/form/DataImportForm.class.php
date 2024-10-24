<?php

namespace wcf\acp\form;

use wcf\data\application\Application;
use wcf\data\object\type\ObjectTypeCache;
use wcf\form\AbstractForm;
use wcf\system\application\ApplicationHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\UserInputException;
use wcf\system\importer\UserImporter;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\StringUtil;

/**
 * Provides the data import form.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class DataImportForm extends AbstractForm
{
    /**
     * additional data
     * @var array
     */
    public $additionalData = [];

    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.maintenance.import';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.management.canImportData'];

    /**
     * list of available exporters
     * @var array
     */
    public $exporters = [];

    /**
     * exporter name
     * @var string
     */
    public $exporterName = '';

    /**
     * exporter object
     * @var \wcf\system\exporter\IExporter
     */
    public $exporter;

    /**
     * list of available importers
     * @var string[]
     */
    public $importers = [];

    /**
     * list of supported data types
     * @var array
     */
    public $supportedData = [];

    /**
     * selected data types
     * @var array
     */
    public $selectedData = [];

    /**
     * database host name
     * @var string
     */
    public $dbHost = '';

    /**
     * database user name
     * @var string
     */
    public $dbUser = '';

    /**
     * database password
     * @var string
     */
    public $dbPassword = '';

    /**
     * database name
     * @var string
     */
    public $dbName = '';

    /**
     * database table prefix
     * @var string
     */
    public $dbPrefix = '';

    /**
     * file system path
     * @var string
     */
    public $fileSystemPath = '';

    /**
     * display notice for existing import mappings
     * @var bool
     */
    public $showMappingNotice = false;

    /**
     * user merge mode
     * @var int
     */
    public $userMergeMode = UserImporter::MERGE_MODE_EMAIL;

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        // get available exporters/importers
        $this->exporters = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.exporter');

        // sort exporters by name
        $collator = new \Collator(WCF::getLanguage()->getLocale());
        \uksort(
            $this->exporters,
            static fn (string $a, string $b) => $collator->compare(
                WCF::getLanguage()->get('wcf.acp.dataImport.exporter.' . $a),
                WCF::getLanguage()->get('wcf.acp.dataImport.exporter.' . $b)
            )
        );

        $this->importers = \array_keys(ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.importer'));

        if (isset($_REQUEST['exporterName'])) {
            $this->exporterName = $_REQUEST['exporterName'];
            if (!isset($this->exporters[$this->exporterName])) {
                throw new IllegalLinkException();
            }

            $this->exporter = $this->exporters[$this->exporterName]->getProcessor();
            $this->supportedData = $this->exporter->getSupportedData();

            // remove unsupported data
            foreach ($this->supportedData as $key => $subData) {
                if (!\in_array($key, $this->importers)) {
                    unset($this->supportedData[$key]);
                    continue;
                }

                foreach ($subData as $key2 => $value) {
                    if (!\in_array($value, $this->importers)) {
                        unset($this->supportedData[$key][$key2]);
                    }
                }
            }

            // get default database prefix
            if (!isset($_POST['dbPrefix'])) {
                $this->dbPrefix = $this->exporter->getDefaultDatabasePrefix();
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function readFormParameters()
    {
        parent::readFormParameters();

        if (isset($_POST['selectedData']) && \is_array($_POST['selectedData'])) {
            $this->selectedData = $_POST['selectedData'];
        }

        if (isset($_POST['dbHost'])) {
            $this->dbHost = StringUtil::trim($_POST['dbHost']);
        }
        if (isset($_POST['dbUser'])) {
            $this->dbUser = StringUtil::trim($_POST['dbUser']);
        }
        if (isset($_POST['dbPassword'])) {
            $this->dbPassword = $_POST['dbPassword'];
        }
        if (isset($_POST['dbName'])) {
            $this->dbName = StringUtil::trim($_POST['dbName']);
        }
        if (isset($_POST['dbPrefix'])) {
            $this->dbPrefix = StringUtil::trim($_POST['dbPrefix']);
        }
        if (isset($_POST['fileSystemPath'])) {
            $this->fileSystemPath = StringUtil::trim($_POST['fileSystemPath']);
        }
        if (isset($_POST['userMergeMode'])) {
            $this->userMergeMode = \intval($_POST['userMergeMode']);
        }
        if (isset($_POST['additionalData'])) {
            $this->additionalData = ArrayUtil::trim($_POST['additionalData']);
        }
    }

    /**
     * @inheritDoc
     */
    public function submit()
    {
        if (!isset($_POST['sourceSelection'])) {
            parent::submit();
        }
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        parent::validate();

        foreach (ApplicationHandler::getInstance()->getAbbreviations() as $abbreviation) {
            if (\realpath(Application::getDirectory($abbreviation)) === \realpath($this->fileSystemPath)) {
                throw new UserInputException('fileSystemPath', 'thisCommunity');
            }
        }

        $this->exporter->setData(
            $this->dbHost,
            $this->dbUser,
            $this->dbPassword,
            $this->dbName,
            $this->dbPrefix,
            $this->fileSystemPath,
            $this->additionalData
        );

        // validate database Access
        try {
            $this->exporter->validateDatabaseAccess();
        } catch (\Exception $e) {
            $exceptions = [];
            do {
                $exceptions[] = $e;
            } while ($e = $e->getPrevious());
            WCF::getTPL()->assign('exceptions', $exceptions);
            throw new UserInputException('database', 'exception');
        }

        // validate selected data
        if (!$this->exporter->validateSelectedData($this->selectedData)) {
            throw new UserInputException('selectedData');
        } elseif (empty($this->exporter->getQueue())) {
            throw new UserInputException('selectedData');
        }

        // validate file access
        if (!$this->exporter->validateFileAccess()) {
            throw new UserInputException('fileSystemPath', 'invalid');
        }

        // validate user merge mode
        switch ($this->userMergeMode) {
            case UserImporter::MERGE_MODE_EMAIL:
            case UserImporter::MERGE_MODE_USERNAME_OR_EMAIL:
                break;
            default:
                $this->userMergeMode = UserImporter::MERGE_MODE_EMAIL;
        }
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        parent::save();

        // get queue
        $queue = $this->exporter->getQueue();

        // save import data
        WCF::getSession()->register('importData', [
            'exporterName' => $this->exporterName,
            'dbHost' => $this->dbHost,
            'dbUser' => $this->dbUser,
            'dbPassword' => $this->dbPassword,
            'dbName' => $this->dbName,
            'dbPrefix' => $this->dbPrefix,
            'fileSystemPath' => $this->fileSystemPath,
            'userMergeMode' => $this->userMergeMode,
            'additionalData' => $this->additionalData,
        ]);

        WCF::getTPL()->assign('queue', $queue);
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        if (!isset($_POST['fileSystemPath'])) {
            $this->fileSystemPath = (!empty($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : WCF_DIR);
        }

        if (empty($_POST)) {
            if (!$this->exporterName) {
                $sql = "SELECT  COUNT(*)
                        FROM    wcf1_import_mapping";
                $statement = WCF::getDB()->prepare($sql);
                $statement->execute();

                if ($statement->fetchSingleColumn()) {
                    $this->showMappingNotice = true;
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'exporter' => $this->exporter,
            'importers' => $this->importers,
            'exporterName' => $this->exporterName,
            'availableExporters' => $this->exporters,
            'supportedData' => $this->supportedData,
            'selectedData' => $this->selectedData,
            'dbHost' => $this->dbHost,
            'dbUser' => $this->dbUser,
            'dbPassword' => $this->dbPassword,
            'dbName' => $this->dbName,
            'dbPrefix' => $this->dbPrefix,
            'fileSystemPath' => $this->fileSystemPath,
            'userMergeMode' => $this->userMergeMode,
            'showMappingNotice' => $this->showMappingNotice,
            'additionalData' => $this->additionalData,
        ]);
    }
}
