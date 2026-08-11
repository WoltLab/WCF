<?php

namespace wcf\system\cli\command;

use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\CLIWCF;
use wcf\system\importer\ImportHandler;
use wcf\system\importer\UserImporter;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Imports data.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class ImportCLICommand implements ICLICommand
{
    /**
     * database host name
     * @var string
     */
    public $dbHost = '';

    /**
     * database name
     * @var string
     */
    public $dbName = '';

    /**
     * database password
     * @var string
     */
    public $dbPassword = '';

    /**
     * database table prefix
     * @var string
     */
    public $dbPrefix = '';

    /**
     * database user name
     * @var string
     */
    public $dbUser = '';

    /**
     * selected exporter
     * @var ?\wcf\system\exporter\IExporter
     */
    protected $exporter;

    /**
     * name of the selected
     * @var string
     */
    public $exporterName = '';

    /**
     * list of available exporters
     * @var ObjectType[]
     */
    protected $exporters = [];

    /**
     * file system path
     * @var string
     */
    public $fileSystemPath = '';

    /**
     * list of available importers
     * @var string[]
     */
    public $importers = [];

    /**
     * indicates if the imported will be quit
     * @var bool
     */
    protected $quitImport = false;

    /**
     * selected data types
     * @var string[]
     */
    public $selectedData = [];

    /**
     * list of supported data types
     * @var array<string, array<string, string>>
     */
    protected $supportedData = [];

    /**
     * user merge mode
     * @var int
     */
    public $userMergeMode = 0;

    #[\Override]
    public function canAccess()
    {
        return WCF::getSession()->hasPermission('admin.management.canImportData');
    }

    #[\Override]
    public function execute(array $parameters)
    {
        CLIWCF::getReader()->setHistoryEnabled(false);

        $this->exporters = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.exporter');
        $this->importers = \array_keys(ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.importer'));

        if ($this->exporters === []) {
            CLIWCF::getReader()->println(WCF::getLanguage()->get('wcf.acp.dataImport.selectExporter.noExporters'));

            return;
        }

        // step 1) previous import
        $sql = "SELECT  COUNT(*)
                FROM    wcf1_import_mapping";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute();
        if ($statement->fetchSingleColumn() > 0) {
            CLIWCF::getReader()->println(StringUtil::stripHTML(WCF::getLanguage()->getDynamicVariable('wcf.acp.dataImport.existingMapping.notice')));
            CLIWCF::getReader()->println(WCF::getLanguage()->getDynamicVariable('wcf.acp.dataImport.existingMapping.confirmMessage') . ' [YN]');

            $answer = CLIWCF::getReader()->readLine('> ');
            if ($answer === null) {
                exit;
            }
            if (\mb_strtolower($answer) === 'y') {
                ImportHandler::getInstance()->resetMapping();
            }
        }

        // step 2) exporter
        $this->readExporter();

        // step 3) selected data
        $this->readSelectedData();
        if ($this->quitImport) {
            CLIWCF::getReader()->setHistoryEnabled(true);

            return;
        }

        // step 4) user merge mode
        $this->readUserMergeMode();

        // step 5) database connection
        $this->readDatabaseConnection();

        // step 6) file system path
        $this->readFileSystemPath();

        // step 7) save import data
        $queue = $this->exporter->getQueue();
        WCF::getSession()->register('importData', [
            'additionalData' => [],
            'dbHost' => $this->dbHost,
            'dbName' => $this->dbName,
            'dbPassword' => $this->dbPassword,
            'dbPrefix' => $this->dbPrefix,
            'dbUser' => $this->dbUser,
            'exporterName' => $this->exporterName,
            'fileSystemPath' => $this->fileSystemPath,
            'userMergeMode' => $this->userMergeMode,
        ]);

        // step 8) import data
        CLIWCF::getReader()->println(
            \sprintf("[%s] %s", \date('c'), WCF::getLanguage()->get('wcf.acp.dataImport.started'))
        );

        foreach ($queue as $objectType) {
            CLIWCF::getReader()->println(
                \sprintf("[%s] %s", \date('c'), WCF::getLanguage()->get('wcf.acp.dataImport.data.' . $objectType))
            );
            $workerCommand = CLICommandHandler::getCommand('worker');
            $workerCommand->execute([
                '--objectType=' . $objectType,
                'ImportWorker',
            ]);
        }

        CLIWCF::getReader()->println(
            \sprintf("[%s] %s", \date('c'), WCF::getLanguage()->get('wcf.acp.dataImport.completed'))
        );

        CLIWCF::getReader()->setHistoryEnabled(true);
    }

    /**
     * Reads the database connection.
     *
     * @return void
     */
    protected function readDatabaseConnection()
    {
        for (;;) {
            CLIWCF::getReader()->println(WCF::getLanguage()->get('wcf.acp.dataImport.configure.database'));
            $dbHost = CLIWCF::getReader()->readLine(WCF::getLanguage()->get('wcf.acp.dataImport.configure.database.host') . '> ');
            if ($dbHost === null) {
                exit;
            }
            $dbUser = CLIWCF::getReader()->readLine(WCF::getLanguage()->get('wcf.acp.dataImport.configure.database.user') . '> ');
            if ($dbUser === null) {
                exit;
            }
            $dbPassword = CLIWCF::getReader()->readLine(
                WCF::getLanguage()->get('wcf.acp.dataImport.configure.database.password') . '> ',
                '*'
            );
            if ($dbPassword === null) {
                exit;
            }
            $dbName = CLIWCF::getReader()->readLine(WCF::getLanguage()->get('wcf.acp.dataImport.configure.database.name') . '> ');
            if ($dbName === null) {
                exit;
            }
            $dbPrefix = CLIWCF::getReader()->readLine(WCF::getLanguage()->get('wcf.acp.dataImport.configure.database.prefix') . '> ');
            if ($dbPrefix === null) {
                exit;
            }

            $this->dbHost = $dbHost;
            $this->dbUser = $dbUser;
            $this->dbPassword = $dbPassword;
            $this->dbName = $dbName;
            $this->dbPrefix = $dbPrefix;

            $this->exporter->setData(
                $this->dbHost,
                $this->dbUser,
                $this->dbPassword,
                $this->dbName,
                $this->dbPrefix,
                '',
                []
            );

            try {
                $this->exporter->validateDatabaseAccess();
            } catch (\Exception $e) {
                $exceptions = [];
                do {
                    $exceptions[] = $e;
                } while ($e = $e->getPrevious());

                $errorMessage = WCF::getLanguage()->getDynamicVariable(
                    'wcf.acp.dataImport.configure.database.error.exception',
                    [
                        'exceptions' => $exceptions,
                    ]
                );
                $errorMessageLines = \explode('<br>', $errorMessage);
                foreach ($errorMessageLines as &$line) {
                    $line = StringUtil::stripHTML($line);
                }
                unset($line);

                foreach ($errorMessageLines as $line) {
                    CLIWCF::getReader()->println($line);
                }
                continue;
            }

            break;
        }
    }

    /**
     * Reads the selected exporter.
     *
     * @return void
     */
    protected function readExporter()
    {
        CLIWCF::getReader()->println(WCF::getLanguage()->get('wcf.acp.dataImport.selectExporter'));
        $exporterSelection = [];
        $exporterIndex = 1;
        foreach ($this->exporters as $objectType) {
            CLIWCF::getReader()->println($exporterIndex . ') ' . WCF::getLanguage()->get('wcf.acp.dataImport.exporter.' . $objectType->objectType));
            $exporterSelection[$exporterIndex++] = $objectType->objectType;
        }
        CLIWCF::getReader()->println(WCF::getLanguage()->getDynamicVariable('wcf.acp.dataImport.cli.selection', [
            'minSelection' => 1,
            'maxSelection' => $exporterIndex - 1,
        ]));

        for (;;) {
            $exporterIndex = CLIWCF::getReader()->readLine(WCF::getLanguage()->get('wcf.acp.dataImport.exporter') . '> ');
            if ($exporterIndex === null) {
                exit;
            }

            if (isset($exporterSelection[$exporterIndex])) {
                $this->exporterName = $exporterSelection[$exporterIndex];
                break;
            }

            CLIWCF::getReader()->println(WCF::getLanguage()->get('wcf.acp.dataImport.selectExporter.error.invalid'));
        }

        $this->exporter = $this->exporters[$this->exporterName]->getProcessor();
        $this->supportedData = $this->exporter->getSupportedData();

        // remove unsupported data
        foreach ($this->supportedData as $objectType => $subData) {
            if (!\in_array($objectType, $this->importers, true)) {
                unset($this->supportedData[$objectType]);
                continue;
            }

            foreach ($subData as $key => $value) {
                if (!\in_array($value, $this->importers, true)) {
                    unset($this->supportedData[$objectType][$key]);
                }
            }
        }
    }

    /**
     * Reads the path to the file system.
     *
     * @return void
     */
    protected function readFileSystemPath()
    {
        CLIWCF::getReader()->println(WCF::getLanguage()->get('wcf.acp.dataImport.configure.fileSystem.path'));
        for (;;) {
            $fileSystemPath = CLIWCF::getReader()->readLine('> ');
            if ($fileSystemPath === null) {
                exit;
            }

            $this->fileSystemPath = $fileSystemPath;
            $this->exporter->setData(
                $this->dbHost,
                $this->dbUser,
                $this->dbPassword,
                $this->dbName,
                $this->dbPrefix,
                $this->fileSystemPath,
                []
            );

            if (!$this->exporter->validateFileAccess()) {
                CLIWCF::getReader()->println(WCF::getLanguage()->get('wcf.acp.dataImport.configure.fileSystem.path.error.invalid'));
                continue;
            }

            break;
        }
    }

    /**
     * Reads the selected data which will be imported.
     *
     * @return void
     */
    protected function readSelectedData()
    {
        $printPrimaryTypes = true;
        $selectedData = [];
        $supportedDataSelection = [
            '' => [],
        ];

        $i = 1;
        $availablePrimaryDataTypes = [];
        foreach ($this->supportedData as $objectType => $subData) {
            $availablePrimaryDataTypes[$i++] = $objectType;
        }
        for (;;) {
            if ($printPrimaryTypes) {
                // print primary import data types
                CLIWCF::getReader()->println(WCF::getLanguage()->get('wcf.acp.dataImport.configure.data.description'));
                $supportedDataIndex = 1;
                $minSupportedDataIndex = 1;
                foreach ($this->supportedData as $objectType => $subData) {
                    if (!isset($selectedData[$objectType])) {
                        CLIWCF::getReader()->println($supportedDataIndex . ') ' . WCF::getLanguage()->get('wcf.acp.dataImport.data.' . $objectType));
                        $supportedDataSelection[''][$supportedDataIndex++] = $objectType;
                    } else {
                        if ($minSupportedDataIndex === $supportedDataIndex) {
                            $minSupportedDataIndex++;
                        }
                        $supportedDataIndex++;
                    }
                }
                CLIWCF::getReader()->println(WCF::getLanguage()->getDynamicVariable(
                    'wcf.acp.dataImport.cli.selection',
                    [
                        'minSelection' => $minSupportedDataIndex,
                        'maxSelection' => $supportedDataIndex - 1,
                    ]
                ));
                $printPrimaryTypes = false;
            }

            // read index of selected primary import data type
            $selectedObjectTypeIndex = CLIWCF::getReader()->readLine(WCF::getLanguage()->get('wcf.acp.dataImport.configure.data') . '> ');
            if ($selectedObjectTypeIndex === null) {
                exit;
            }

            // if no primary import data type is selected, finish data selection
            if ($selectedObjectTypeIndex === '') {
                // if no data is selected, quit import
                if ($selectedData === []) {
                    CLIWCF::getReader()->println(WCF::getLanguage()->get('wcf.acp.dataImport.cli.configure.data.error.noSelection'));
                    $this->quitImport = true;

                    return;
                }
                break;
            }

            // validate selected primary import data type
            if (isset($supportedDataSelection[''][$selectedObjectTypeIndex])) {
                $selectedObjectType = $supportedDataSelection[''][$selectedObjectTypeIndex];
                $selectedData[$selectedObjectType] = [];
                unset($supportedDataSelection[''][$selectedObjectTypeIndex]);
            } elseif (isset($availablePrimaryDataTypes[$selectedObjectTypeIndex])) {
                CLIWCF::getReader()->println(WCF::getLanguage()->get('wcf.acp.dataImport.cli.configure.data.alreadySelected'));
                continue;
            } else {
                CLIWCF::getReader()->println(WCF::getLanguage()->get('wcf.acp.dataImport.cli.configure.data.error.invalid'));
                continue;
            }

            // handle secondary import data types
            if (!empty($this->supportedData[$selectedObjectType])) {
                // print secondary import data types
                CLIWCF::getReader()->println('  ' . WCF::getLanguage()->get('wcf.acp.dataImport.configure.data.description'));
                CLIWCF::getReader()->println('  0) ' . WCF::getLanguage()->get('wcf.acp.dataImport.cli.configure.data.selectAll'));

                $supportedDataSelection[$selectedObjectType] = [];
                $supportedDataIndex = 1;
                foreach ($this->supportedData[$selectedObjectType] as $objectType) {
                    CLIWCF::getReader()->println('  ' . $supportedDataIndex . ') ' . WCF::getLanguage()->get('wcf.acp.dataImport.data.' . $objectType));
                    $supportedDataSelection[$selectedObjectType][$supportedDataIndex++] = $objectType;
                }
                CLIWCF::getReader()->println('  ' . WCF::getLanguage()->getDynamicVariable(
                    'wcf.acp.dataImport.cli.selection',
                    [
                        'minSelection' => 0,
                        'maxSelection' => $supportedDataIndex - 1,
                    ]
                ));

                for (;;) {
                    // read index of selected secondary import data type
                    $selectedSecondaryObjectTypeIndex = CLIWCF::getReader()->readLine('  ' . WCF::getLanguage()->get('wcf.acp.dataImport.configure.data') . '> ');
                    if ($selectedSecondaryObjectTypeIndex === null) {
                        exit;
                    }

                    // continue with primary import data type selection
                    if ($selectedSecondaryObjectTypeIndex === '') {
                        break;
                    }

                    // validate selected secondary import data type
                    if ($selectedSecondaryObjectTypeIndex === '0') {
                        // selected all secondary import data type
                        $selectedData[$selectedObjectType] = \array_merge(
                            $selectedData[$selectedObjectType],
                            $supportedDataSelection[$selectedObjectType]
                        );
                        break;
                    } elseif (isset($supportedDataSelection[$selectedObjectType][$selectedSecondaryObjectTypeIndex])) {
                        $selectedSecondaryObjectType = $supportedDataSelection[$selectedObjectType][$selectedSecondaryObjectTypeIndex];
                        $selectedData[$selectedObjectType][$selectedSecondaryObjectTypeIndex] = $selectedSecondaryObjectType;
                        unset($supportedDataSelection[$selectedObjectType][$selectedSecondaryObjectTypeIndex]);
                    } elseif (isset($selectedData[$selectedObjectType][$selectedSecondaryObjectTypeIndex])) {
                        CLIWCF::getReader()->println('  ' . WCF::getLanguage()->get('wcf.acp.dataImport.cli.configure.data.alreadySelected'));
                        continue;
                    } else {
                        CLIWCF::getReader()->println('  ' . WCF::getLanguage()->get('wcf.acp.dataImport.cli.configure.data.error.invalid'));
                        continue;
                    }

                    // check if all possible secondary import data types are selected
                    if (\count($selectedData[$selectedObjectType]) === \count($this->supportedData[$selectedObjectType])) {
                        $printPrimaryTypes = true;
                        break;
                    }
                }

                if (!empty($supportedDataSelection[$selectedObjectType])) {
                    $printPrimaryTypes = true;
                }
            }

            // check if all possible primary import data types are selected
            if (\count($selectedData) === \count($this->supportedData)) {
                break;
            }
        }

        foreach ($selectedData as $objectType => $objectTypes) {
            $this->selectedData[] = $objectType;
            $this->selectedData = \array_merge($this->selectedData, $objectTypes);
        }

        if (!$this->exporter->validateSelectedData($this->selectedData)) {
            CLIWCF::getReader()->println(WCF::getLanguage()->get('wcf.acp.dataImport.cli.configure.data.error.noSelection'));
            $this->quitImport = true;
        }
    }

    /**
     * Reads the user merge mode.
     *
     * @return void
     */
    protected function readUserMergeMode()
    {
        CLIWCF::getReader()->println(WCF::getLanguage()->get('wcf.acp.dataImport.configure.settings.userMergeMode'));
        CLIWCF::getReader()->println('1) ' . WCF::getLanguage()->get('wcf.acp.dataImport.configure.settings.userMergeMode.4') . ' (*)');
        CLIWCF::getReader()->println('2) ' . WCF::getLanguage()->get('wcf.acp.dataImport.configure.settings.userMergeMode.5'));
        CLIWCF::getReader()->println(WCF::getLanguage()->getDynamicVariable('wcf.acp.dataImport.cli.selection', [
            'minSelection' => 1,
            'maxSelection' => 2,
        ]));

        for (;;) {
            $userMergeMode = CLIWCF::getReader()->readLine('> ');
            if ($userMergeMode === null) {
                exit;
            }

            $this->userMergeMode = match ((int)$userMergeMode) {
                1 => UserImporter::MERGE_MODE_EMAIL,
                2 => UserImporter::MERGE_MODE_USERNAME_OR_EMAIL,
                default => UserImporter::MERGE_MODE_EMAIL,
            };

            break;
        }
    }
}
