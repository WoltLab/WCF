<?php

namespace wcf\acp\page;

use wcf\acp\action\FirstTimeSetupAction;
use wcf\data\devtools\missing\language\item\DevtoolsMissingLanguageItemList;
use wcf\data\package\installation\queue\PackageInstallationQueue;
use wcf\data\user\User;
use wcf\page\AbstractPage;
use wcf\system\acp\dashboard\AcpDashboard;
use wcf\system\application\ApplicationHandler;
use wcf\system\cache\builder\OptionCacheBuilder;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\Environment;
use wcf\system\registry\RegistryHandler;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;

/**
 * Shows the welcome page in admin control panel.
 *
 * @author  Marcel Werk
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class IndexPage extends AbstractPage
{
    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.general.canUseAcp'];

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

        $sql = "SELECT @@innodb_flush_log_at_trx_commit";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute();
        $innodbFlushLogAtTrxCommit = $statement->fetchSingleColumn();

        $this->server = [
            'os' => \PHP_OS,
            'webserver' => $_SERVER['SERVER_SOFTWARE'] ?? '',
            'mySQLVersion' => WCF::getDB()->getVersion(),
            'load' => '',
            'memoryLimit' => \ini_get('memory_limit'),
            'upload_max_filesize' => \ini_get('upload_max_filesize'),
            'postMaxSize' => \ini_get('post_max_size'),
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
            $conditionBuilder->add('banned = ?', [0]);
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

        $optionCategories = OptionCacheBuilder::getInstance()->getData([], 'categories');
        $recaptchaWithoutKey = false;
        $recaptchaKeyLink = '';
        if (CAPTCHA_TYPE == 'com.woltlab.wcf.recaptcha' && (!RECAPTCHA_PUBLICKEY || !RECAPTCHA_PRIVATEKEY)) {
            $recaptchaWithoutKey = true;

            $recaptchaKeyLink = LinkHandler::getInstance()->getLink(
                'Option',
                [
                    'id' => $optionCategories['security']->categoryID,
                    'optionName' => 'recaptcha_publickey',
                ],
                '#category_security.antispam'
            );
        }

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

        $storedSystemId = RegistryHandler::getInstance()->get(
            'com.woltlab.wcf',
            Environment::SYSTEM_ID_REGISTRY_KEY
        );
        $systemIdMismatch = $storedSystemId !== Environment::getSystemId();

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

        $sql = "SELECT DATABASE()";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute();
        $databaseName = $statement->fetchSingleColumn();

        WCF::getTPL()->assign([
            'recaptchaWithoutKey' => $recaptchaWithoutKey,
            'recaptchaKeyLink' => $recaptchaKeyLink,
            'server' => $this->server,
            'databaseName' => $databaseName,
            'usersAwaitingApproval' => $usersAwaitingApproval,
            'evaluationExpired' => $evaluationExpired,
            'evaluationPending' => $evaluationPending,
            'taintedApplications' => $taintedApplications,
            'systemIdMismatch' => $systemIdMismatch,
            'missingLanguageItemsMTime' => $missingLanguageItemsMTime,
            'dashboard' => new AcpDashboard(),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function show()
    {
        // check package installation queue
        if (!\PACKAGE_ID && $this->action == 'WCFSetup') {
            $queue = new PackageInstallationQueue(1);

            \assert($queue->queueID === 1);
            \assert($queue->parentQueueID === 0);
            \assert($queue->package === 'com.woltlab.wcf');

            WCF::getTPL()->assign(['queueID' => $queue->queueID]);
            WCF::getTPL()->display('packageInstallationSetup');

            exit;
        }

        if (\FIRST_TIME_SETUP_STATE != -1) {
            HeaderUtil::redirect(LinkHandler::getInstance()->getControllerLink(
                FirstTimeSetupAction::class,
            ));

            exit;
        }

        // show page
        parent::show();
    }
}
