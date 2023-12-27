<?php

namespace wcf\acp\action;

use Laminas\Diactoros\Response\JsonResponse;
use wcf\action\AbstractAction;
use wcf\data\package\PackageCache;
use wcf\data\user\cover\photo\DefaultUserCoverPhoto;
use wcf\data\user\group\UserGroup;
use wcf\data\user\UserProfile;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\database\statement\PreparedStatement;
use wcf\system\event\EventHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\option\user\UserOptionHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;
use wcf\util\UserUtil;

/**
 * Exports the stored data of a user in compliance with Art. 20 "Right to data portability" of the
 * the General Data Protection Regulation (GDPR) of the European Union.
 *
 * The file formats XML, JSON and CSV are explicitly listed as being a structured
 * and machine-readable format by the European Commission.
 * See https://ec.europa.eu/info/law/law-topic/data-protection/reform/rules-business-and-organisations/dealing-citizens/can-individuals-ask-have-their-data-transferred-another-organisation_en
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class UserExportGdprAction extends AbstractAction
{
    /**
     * export data
     *
     * @var array
     */
    public $data = [];

    /**
     * list of column names of the user table, the columns `languageID` and `registrationIpAddress`
     * are not listed here, but included in the final output due to special handling
     *
     * these properties are always exported, even if PHP considers them to be empty
     *
     * @var string[]
     */
    public $exportUserProperties = [
        'username',
        'email',
    ];

    /**
     * list of column names of the user table, the columns `languageID` and `registrationIpAddress`
     * are not listed here, but included in the final output due to special handling
     *
     * these properties are only exported if PHP does not consider them to be empty
     *
     * @var string[]
     */
    public $exportUserPropertiesIfNotEmpty = [
        'registrationDate',
        'oldUsername',
        'lastUsernameChange',
        'signature',
        'lastActivityTime',
        'userTitle',
        'authData',
    ];

    /**
     * list of user options that are associated with a settings.* category, but should be included
     * in the export regardless
     *
     * these settings are always exported, even if PHP considers them to be empty
     *
     * @var string[]
     */
    public $exportUserOptionSettings = [];

    /**
     * list of user options that are associated with a settings.* category, but should be included
     * in the export regardless
     *
     * these settings are only exported if PHP does not consider them to be empty
     *
     * @var string[]
     */
    public $exportUserOptionSettingsIfNotEmpty = ['timezone'];

    /**
     * list of database tables that hold ip addresses, the identifier is used to check if the
     * package is installed and on success exports the data from all listed table names
     *
     * @var string[][]
     */
    public $ipAddresses = [];

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.user.canExportGdprData'];

    /**
     * list of user option names that are excluded from the output, any option that begins with
     * `can*` or `admin*` are excluded by default, as well as any option that ends with `*perPage`
     *
     * @var string[]
     */
    public $skipUserOptions = [
        'birthdayShowYear',
        'showSignature',
        'watchThreadOnReply',
    ];

    /**
     * @var UserProfile
     */
    public $user;

    /**
     * @var int
     */
    public $userID = 0;

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (isset($_GET['id'])) {
            $this->userID = \intval($_GET['id']);
        }

        $this->user = UserProfileRuntimeCache::getInstance()->getObject($this->userID);
        if ($this->user === null) {
            throw new IllegalLinkException();
        }

        if (!UserGroup::isAccessibleGroup($this->user->getGroupIDs())) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        // you MUST NOT use the `execute` event to provide data, use `export` (see below) instead!
        parent::execute();

        $this->ipAddresses = [
            'com.woltlab.blog' => ['blog' . WCF_N . '_entry '],
            'com.woltlab.calendar' => ['calendar' . WCF_N . '_event'],
            'com.woltlab.filebase' => [
                'filebase' . WCF_N . '_file',
            ],
            'com.woltlab.gallery' => [],
            // intentionally left empty, the image table is queried manually
            'com.woltlab.wbb' => ['wbb' . WCF_N . '_post'],
            'com.woltlab.wcf.conversation' => ['wcf' . WCF_N . '_conversation_message'],
        ];

        // content
        $this->data = [
            'com.woltlab.wcf' => [
                'user' => [],
                'userOptions' => [],
                'ipAddresses' => [],
                'paidSubscriptionTransactionLog' => $this->dumpTable(
                    'wcf' . WCF_N . '_paid_subscription_transaction_log',
                    'userID'
                ),
            ],
        ];

        EventHandler::getInstance()->fireAction($this, 'export');

        $this->data['com.woltlab.wcf']['user'] = \array_merge(
            $this->data['com.woltlab.wcf']['user'],
            $this->exportUser()
        );
        $this->data['com.woltlab.wcf']['userOptions'] = \array_merge(
            $this->data['com.woltlab.wcf']['userOptions'],
            $this->exportUserOptions()
        );
        $this->data['com.woltlab.wcf']['ipAddresses'] = \array_merge(
            $this->data['com.woltlab.wcf']['ipAddresses'],
            $this->exportSessionIpAddresses()
        );

        foreach ($this->ipAddresses as $package => $tableNames) {
            if (PackageCache::getInstance()->getPackageByIdentifier($package) === null) {
                continue;
            }

            $ipAddresses = [];
            foreach ($tableNames as $tableName) {
                $ipAddresses = \array_merge(
                    $ipAddresses,
                    $this->exportIpAddresses($tableName, 'ipAddress', 'time', 'userID')
                );
            }

            if ($package === 'com.woltlab.filebase') {
                $ipAddresses = \array_merge(
                    $ipAddresses,
                    $this->exportIpAddresses('filebase' . WCF_N . '_file_version', 'ipAddress', 'uploadTime', 'userID')
                );
            } elseif ($package === 'com.woltlab.gallery') {
                $ipAddresses = \array_merge(
                    $ipAddresses,
                    $this->exportIpAddresses('gallery' . WCF_N . '_image', 'ipAddress', 'uploadTime', 'userID')
                );
            }

            if (!empty($ipAddresses)) {
                if (!isset($this->data[$package])) {
                    $this->data[$package] = [];
                }
                $this->data[$package]['ipAddresses'] = $ipAddresses;
            }
        }

        $this->data['@@generatedAt'] = TIME_NOW;

        $this->executed();

        return HeaderUtil::withNoCacheHeaders(new JsonResponse(
            $this->data,
            200,
            [
                'content-disposition' => 'attachment; filename="user-export-gdpr-' . $this->user->userID . '.json"',
            ],
            JsonResponse::DEFAULT_JSON_FLAGS | \JSON_PRETTY_PRINT
        ));
    }

    /**
     * Exports the list of stored ip addresses for this user using the IPv4 representation
     * whenever possible.
     *
     * @param string $databaseTable
     * @param string $ipAddressColumn
     * @param string $timeColumn
     * @param string $userIDColumn
     * @return      array
     */
    public function exportIpAddresses($databaseTable, $ipAddressColumn, $timeColumn, $userIDColumn)
    {
        $sql = "SELECT  {$ipAddressColumn}, {$timeColumn}
                FROM    {$databaseTable}
                WHERE   {$userIDColumn} = ?
                    AND {$ipAddressColumn} <> ''";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([$this->user->userID]);

        return $this->fetchIpAddresses($statement, $ipAddressColumn, $timeColumn);
    }

    protected function dumpTable($tableName, $userIDColumn)
    {
        $sql = "SELECT  *
                FROM    {$tableName}
                WHERE   {$userIDColumn} = ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([$this->user->userID]);

        $data = [];
        while ($row = $statement->fetchArray()) {
            $data[] = $row;
        }

        return $data;
    }

    protected function fetchIpAddresses(PreparedStatement $statement, $ipAddressColumn, $timeColumn)
    {
        $ipAddresses = [];

        while ($row = $statement->fetchArray()) {
            if (!$row[$ipAddressColumn]) {
                continue;
            }

            $ipAddresses[] = [
                'ipAddress' => UserUtil::convertIPv6To4($row[$ipAddressColumn]),
                'time' => $row[$timeColumn],
            ];
        }

        return $ipAddresses;
    }

    protected function exportSessionIpAddresses()
    {
        $data = [
            'session' => [],
            'acpSessionLog' => [],
        ];

        $data['session'] = $this->exportIpAddresses(
            'wcf' . WCF_N . '_user_session',
            'ipAddress',
            'lastActivityTime',
            'userID'
        );

        // we can ignore the wcfN_acp_session_access_log table because it is directly related
        // to the wcfN_acp_session_log table and ACP sessions are bound to the ip address
        $data['acpSessionLog'] = $this->exportIpAddresses(
            'wcf' . WCF_N . '_acp_session_log',
            'ipAddress',
            'lastActivityTime',
            'userID'
        );

        return $data;
    }

    protected function exportUser()
    {
        $data = ['languageCode' => $this->user->getLanguage()->getFixedLanguageCode()];
        if ($this->user->registrationIpAddress) {
            $data['registrationIpAddress'] = UserUtil::convertIPv6To4($this->user->registrationIpAddress);
        }

        foreach ($this->exportUserProperties as $property) {
            $data[$property] = $this->user->{$property};
        }
        foreach ($this->exportUserPropertiesIfNotEmpty as $property) {
            if ($this->user->{$property}) {
                $data[$property] = $this->user->{$property};
            }
        }

        if ($this->user->avatarID) {
            $data['avatarURL'] = $this->user->getAvatar()->getURL();
        }

        $coverPhoto = $this->user->getCoverPhoto(true);
        if (!($coverPhoto instanceof DefaultUserCoverPhoto)) {
            $data['coverPhotoURL'] = $coverPhoto->getURL();
        }

        return $data;
    }

    protected function exportUserOptions()
    {
        $optionHandler = new UserOptionHandler(false, '', '');
        $optionHandler->init();
        $optionTree = $optionHandler->getOptionTree();

        $data = [];
        foreach ($optionTree as $category) {
            $this->exportUserOptionCategory($data, $category);
        }

        return $data;
    }

    protected function exportUserOptionCategory(array &$data, array $optionTree)
    {
        if (!empty($optionTree['options'])) {
            foreach ($optionTree['options'] as $optionData) {
                $option = $optionData['object'];

                if (\in_array($option->optionName, $this->skipUserOptions)) {
                    // blacklisted option name
                    continue;
                } else {
                    if (\preg_match('~(?:^(?:admin|can)[A-Z]|PerPage$)~', $option->optionName)) {
                        // ignore any option that begins with `admin*` and `can*`, or ends with `*perPage`
                        continue;
                    }
                }

                $forceExport = \in_array($option->optionName, $this->exportUserOptionSettings);

                // ignore settings unless they are explicitly white-listed
                if (!$forceExport && \strpos($option->categoryName, 'settings.') === 0) {
                    if (!\in_array($option->optionName, $this->exportUserOptionSettingsIfNotEmpty)) {
                        continue;
                    }
                }

                $optionValue = $this->user->getUserOption($option->optionName);
                if ($option->optionType === 'boolean') {
                    $optionValue = ($optionValue == 1);
                } else {
                    if ($option->optionType === 'select' || $option->optionType === 'timezone') {
                        $formattedValue = $this->user->getFormattedUserOption($option->optionName);
                        if ($formattedValue) {
                            $optionValue = $formattedValue;
                        }
                    }
                }

                // skip empty string values (but not values that resolve to `false` or `0`
                if (!$forceExport) {
                    if ($optionValue === '') {
                        continue;
                    } else {
                        if ($option->optionName === 'gender' && $optionValue === '0') {
                            // exclude the gender if there has been no selection
                            continue;
                        }
                    }
                }

                $data[$option->optionName] = $optionValue;
            }
        }

        if (!empty($optionTree['categories'])) {
            foreach ($optionTree['categories'] as $category) {
                $this->exportUserOptionCategory($data, $category);
            }
        }
    }
}
