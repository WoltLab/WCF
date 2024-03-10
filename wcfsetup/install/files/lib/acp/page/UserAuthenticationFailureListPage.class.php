<?php

namespace wcf\acp\page;

use wcf\data\user\authentication\failure\UserAuthenticationFailureList;
use wcf\page\SortablePage;
use wcf\system\WCF;

/**
 * Shows a list of user authentication failures.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property    UserAuthenticationFailureList $objectList
 */
class UserAuthenticationFailureListPage extends SortablePage
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.log.authentication.failure';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.management.canViewLog'];

    /**
     * @inheritDoc
     */
    public $neededModules = ['ENABLE_USER_AUTHENTICATION_FAILURE'];

    /**
     * @inheritDoc
     */
    public $defaultSortField = 'time';

    /**
     * @inheritDoc
     */
    public $defaultSortOrder = 'DESC';

    /**
     * @inheritDoc
     */
    public $validSortFields = ['failureID', 'environment', 'userID', 'username', 'time', 'ipAddress', 'userAgent', 'validationError'];

    /**
     * @inheritDoc
     */
    public $objectListClassName = UserAuthenticationFailureList::class;

    /**
     * @var string[]
     * @since   5.4
     */
    public $filter = [
        'environment' => '',
        'endDate' => '',
        'startDate' => '',
        'username' => '',
        'userAgent' => '',
        'validationError' => '',
    ];

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (isset($_REQUEST['filter']) && \is_array($_REQUEST['filter'])) {
            foreach ($_REQUEST['filter'] as $key => $value) {
                if (\array_key_exists($key, $this->filter)) {
                    $this->filter[$key] = $value;
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    protected function initObjectList()
    {
        parent::initObjectList();

        if ($this->filter['environment'] !== '') {
            $this->objectList->getConditionBuilder()->add(
                'user_authentication_failure.environment = ?',
                [$this->filter['environment']]
            );
        }
        if ($this->filter['endDate'] !== '') {
            $endDate = @\strtotime($this->filter['endDate']);
            if ($endDate > 0) {
                $this->objectList->getConditionBuilder()->add(
                    'user_authentication_failure.time <= ?',
                    [$endDate]
                );
            }
        }
        if ($this->filter['startDate'] !== '') {
            $startDate = @\strtotime($this->filter['startDate']);
            if ($startDate > 0) {
                $this->objectList->getConditionBuilder()->add(
                    'user_authentication_failure.time >= ?',
                    [$startDate]
                );
            }
        }
        if ($this->filter['username'] !== '') {
            $this->objectList->getConditionBuilder()->add(
                'user_authentication_failure.username LIKE ?',
                ['%' . \addcslashes($this->filter['username'], '_%') . '%']
            );
        }
        if ($this->filter['userAgent'] !== '') {
            $this->objectList->getConditionBuilder()->add(
                'user_authentication_failure.userAgent LIKE ?',
                ['%' . \addcslashes($this->filter['userAgent'], '_%') . '%']
            );
        }
        if ($this->filter['validationError'] !== '') {
            $this->objectList->getConditionBuilder()->add(
                'user_authentication_failure.validationError = ?',
                [$this->filter['validationError']]
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        $filterLinkParameters = \http_build_query(['filter' => \array_filter($this->filter)], '', '&');

        WCF::getTPL()->assign([
            'filter' => $this->filter,
            'filterLinkParameters' => $filterLinkParameters ? '&' . $filterLinkParameters : '',
        ]);
    }
}
