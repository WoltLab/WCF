<?php

namespace wcf\page;

use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\menu\user\UserMenu;
use wcf\system\session\Session;
use wcf\system\session\SessionHandler;
use wcf\system\user\multifactor\Setup;
use wcf\system\WCF;

/**
 * Shows the account security page.
 *
 * @author  Tim Duesterhus, Joshua Ruesweg
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       5.4
 */
class AccountSecurityPage extends AbstractPage
{
    /**
     * @inheritDoc
     */
    public $loginRequired = true;

    /**
     * @var Session[]
     */
    private $activeSessions;

    /**
     * @var ObjectType[]
     */
    private $multifactorMethods;

    /**
     * @var Setup[]
     */
    private $enabledMultifactorMethods;

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        $this->activeSessions = SessionHandler::getInstance()->getUserSessions(WCF::getUser());

        \usort($this->activeSessions, static function (Session $a, Session $b) {
            return $b->getLastActivityTime() <=> $a->getLastActivityTime();
        });

        $this->multifactorMethods = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.multifactor');

        $setups = Setup::getAllForUser(WCF::getUser());
        foreach ($setups as $setup) {
            $this->enabledMultifactorMethods[$setup->getObjectType()->objectTypeID] = $setup;
        }

        \usort($this->multifactorMethods, function (ObjectType $a, ObjectType $b) {
            $aEnabled = isset($this->enabledMultifactorMethods[$a->objectTypeID]) ? 1 : 0;
            $bEnabled = isset($this->enabledMultifactorMethods[$b->objectTypeID]) ? 1 : 0;

            return $bEnabled <=> $aEnabled ?: $b->priority <=> $a->priority;
        });
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'activeSessions' => $this->activeSessions,
            'multifactorMethods' => $this->multifactorMethods,
            'enabledMultifactorMethods' => $this->enabledMultifactorMethods,
            'requiresMultifactor' => WCF::getUser()->requiresMultifactor() && !WCF::getUser()->multifactorActive,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function show()
    {
        // set active tab
        UserMenu::getInstance()->setActiveMenuItem('wcf.user.menu.profile.security');

        parent::show();
    }
}
