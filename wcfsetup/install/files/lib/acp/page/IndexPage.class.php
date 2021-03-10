<?php

namespace wcf\acp\page;

use wcf\data\devtools\missing\language\item\DevtoolsMissingLanguageItemList;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\User;
use wcf\page\AbstractPage;
use wcf\system\application\ApplicationHandler;
use wcf\system\cache\builder\OptionCacheBuilder;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\io\RemoteFile;
use wcf\system\package\PackageInstallationDispatcher;
use wcf\system\request\LinkHandler;
use wcf\system\search\SearchIndexManager;
use wcf\system\WCF;

/**
 * Shows the welcome page in admin control panel.
 *
 * @author  Marcel Werk
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Acp\Page
 */
class IndexPage extends AbstractPage
{
    /**
     * server information
     * @var string[]
     */
    public $server = [];

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        $sql = "SHOW VARIABLES LIKE 'innodb_flush_log_at_trx_commit'";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute();
        $row = $statement->fetchArray();
        $innodbFlushLogAtTrxCommit = false;
        if ($row !== false) {
            $innodbFlushLogAtTrxCommit = $row['Value'];
        }

        $this->server = [
            'os' => \PHP_OS,
            'webserver' => $_SERVER['SERVER_SOFTWARE'] ?? '',
            'mySQLVersion' => WCF::getDB()->getVersion(),
            'load' => '',
            'memoryLimit' => @\ini_get('memory_limit'),
            'upload_max_filesize' => @\ini_get('upload_max_filesize'),
            'postMaxSize' => @\ini_get('post_max_size'),
            'sslSupport' => RemoteFile::supportsSSL(),
            'innodbFlushLogAtTrxCommit' => $innodbFlushLogAtTrxCommit,
        ];

        // get load
        if (\function_exists('sys_getloadavg')) {
            $load = \sys_getloadavg();
            if (\is_array($load) && \count($load) == 3) {
                $this->server['load'] = \implode(
                    ', ',
                    \array_map(static function (float $value) {
                        return \sprintf('%.2F', $value);
                    }, $load)
                );
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        $usersAwaitingApproval = 0;
        if (REGISTER_ACTIVATION_METHOD & User::REGISTER_ACTIVATION_ADMIN) {
            $conditionBuilder = new PreparedStatementConditionBuilder();
            $conditionBuilder->add('activationCode <> ?', [0]);
            if (REGISTER_ACTIVATION_METHOD & User::REGISTER_ACTIVATION_USER) {
                $conditionBuilder->add('emailConfirmed IS NULL');
            }

            $sql = "SELECT  COUNT(*)
                    FROM    wcf" . WCF_N . "_user "
                . $conditionBuilder;
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute($conditionBuilder->getParameters());
            $usersAwaitingApproval = $statement->fetchSingleColumn();
        }

        $recaptchaWithoutKey = false;
        $recaptchaKeyLink = '';
        if (CAPTCHA_TYPE == 'com.woltlab.wcf.recaptcha' && (!RECAPTCHA_PUBLICKEY || !RECAPTCHA_PRIVATEKEY)) {
            $recaptchaWithoutKey = true;

            $optionCategories = OptionCacheBuilder::getInstance()->getData([], 'categories');
            $categorySecurity = $optionCategories['security'];
            $recaptchaKeyLink = LinkHandler::getInstance()->getLink(
                'Option',
                [
                    'id' => $categorySecurity->categoryID,
                    'optionName' => 'recaptcha_publickey',
                ],
                '#category_security.antispam'
            );
        }

        $objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.searchableObjectType');
        $tableNames = [];
        foreach ($objectTypes as $objectType) {
            $tableNames[] = SearchIndexManager::getTableName($objectType->objectType);
        }
        $conditionBuilder = new PreparedStatementConditionBuilder(true);
        $conditionBuilder->add('TABLE_NAME IN (?)', [$tableNames]);
        $conditionBuilder->add('TABLE_SCHEMA = ?', [WCF::getDB()->getDatabaseName()]);
        $conditionBuilder->add('ENGINE <> ?', ['InnoDB']);

        $sql = "SELECT  COUNT(*)
                FROM    INFORMATION_SCHEMA.TABLES
                " . $conditionBuilder;
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute($conditionBuilder->getParameters());
        $nonInnoDbSearch = $statement->fetchSingleColumn() > 0;

        $evaluationExpired = $evaluationPending = [];
        foreach (ApplicationHandler::getInstance()->getApplications() as $application) {
            if ($application->isTainted) {
                continue;
            }

            if ($application->getPackage()->package === 'com.woltlab.wcf') {
                continue;
            }

            $app = WCF::getApplicationObject($application);
            $endDate = $app->getEvaluationEndDate();
            if ($endDate) {
                if ($endDate < TIME_NOW) {
                    $pluginStoreFileID = $app->getEvaluationPluginStoreID();
                    $isWoltLab = false;
                    if (
                        $pluginStoreFileID === 0 && \strpos(
                            $application->getPackage()->package,
                            'com.woltlab.'
                        ) === 0
                    ) {
                        $isWoltLab = true;
                    }

                    $evaluationExpired[] = [
                        'packageName' => $application->getPackage()->getName(),
                        'isWoltLab' => $isWoltLab,
                        'pluginStoreFileID' => $pluginStoreFileID,
                    ];
                } else {
                    if (!isset($evaluationPending[$endDate])) {
                        $evaluationPending[$endDate] = [];
                    }

                    $evaluationPending[$endDate][] = $application->getPackage()->getName();
                }
            }
        }

        $taintedApplications = [];
        foreach (ApplicationHandler::getInstance()->getApplications() as $application) {
            if (!$application->isTainted) {
                continue;
            }

            $taintedApplications[$application->getPackage()->packageID] = $application;
        }

        $missingLanguageItemsMTime = 0;
        if (ENABLE_DEBUG_MODE && ENABLE_DEVELOPER_TOOLS) {
            $logList = new DevtoolsMissingLanguageItemList();
            $logList->sqlOrderBy = 'lastTime DESC';
            $logList->sqlLimit = 1;
            $logList->readObjects();
            $logEntry = $logList->getSingleObject();

            if ($logEntry !== null) {
                $missingLanguageItemsMTime = $logEntry->lastTime;
            }
        }

        WCF::getTPL()->assign([
            'recaptchaWithoutKey' => $recaptchaWithoutKey,
            'recaptchaKeyLink' => $recaptchaKeyLink,
            'nonInnoDbSearch' => $nonInnoDbSearch,
            'server' => $this->server,
            'usersAwaitingApproval' => $usersAwaitingApproval,
            'evaluationExpired' => $evaluationExpired,
            'evaluationPending' => $evaluationPending,
            'taintedApplications' => $taintedApplications,
            'missingLanguageItemsMTime' => $missingLanguageItemsMTime,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function show()
    {
        // check package installation queue
        if ($this->action == 'WCFSetup') {
            $queueID = PackageInstallationDispatcher::checkPackageInstallationQueue();

            if ($queueID) {
                WCF::getTPL()->assign(['queueID' => $queueID]);
                WCF::getTPL()->display('packageInstallationSetup');

                exit;
            }
        }

        // show page
        parent::show();
    }
}
