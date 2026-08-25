<?php

namespace wcf\data\user\object\watch;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * Executes watched object-related actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectAction<UserObjectWatch, UserObjectWatchEditor>
 */
class UserObjectWatchAction extends AbstractDatabaseObjectAction
{
    /**
     * object type object
     * @var ?\wcf\data\object\type\ObjectType
     */
    protected $objectType;

    /**
     * user object watch object
     * @var ?UserObjectWatch
     */
    protected $userObjectWatch;

    /**
     * Validates parameters to manage a subscription.
     *
     * @return void
     */
    public function validateManageSubscription()
    {
        $this->readInteger('objectID');
        $this->readString('objectType');

        // validate object type
        $this->objectType = ObjectTypeCache::getInstance()->getObjectTypeByName(
            'com.woltlab.wcf.user.objectWatch',
            $this->parameters['objectType']
        );
        if ($this->objectType === null) {
            throw new UserInputException('objectType');
        }

        // validate object id
        $this->objectType->getProcessor()->validateObjectID($this->parameters['objectID']);

        // get existing subscription
        $this->userObjectWatch = UserObjectWatch::getUserObjectWatch(
            $this->objectType->objectTypeID,
            WCF::getUser()->userID,
            $this->parameters['objectID']
        );
    }

    /**
     * Returns a form to manage a subscription.
     *
     * @return array{objectID: int, objectType: string, template: string}
     */
    public function manageSubscription()
    {
        return [
            'objectID' => $this->parameters['objectID'],
            'objectType' => $this->parameters['objectType'],
            'template' => WCF::getTPL()->render('wcf', 'manageSubscription', [
                'objectType' => $this->objectType,
                'userObjectWatch' => $this->userObjectWatch,
            ]),
        ];
    }

    /**
     * Validates parameters to save subscription state.
     *
     * @return void
     */
    public function validateSaveSubscription()
    {
        $this->readBoolean('enableNotification');
        $this->readBoolean('subscribe');

        $this->validateManageSubscription();
    }

    /**
     * Saves subscription state.
     *
     * @return array{objectID: int, objectType: int, subscribe: 0|1|bool}
     */
    public function saveSubscription()
    {
        // subscribe
        if ($this->parameters['subscribe'] === true) {
            // newly subscribed
            if ($this->userObjectWatch === null) {
                UserObjectWatchBuilder::forCreate()
                    ->setObjectType($this->objectType)
                    ->setObjectID($this->parameters['objectID'])
                    ->setUser(WCF::getUser())
                    ->setNotification($this->parameters['enableNotification'])
                    ->createOrIgnore();
            } elseif ((bool)$this->userObjectWatch->notification !== $this->parameters['enableNotification']) {
                // update notification type
                UserObjectWatchBuilder::forUpdate($this->userObjectWatch)
                    ->setNotification($this->parameters['enableNotification'])
                    ->update();
            }

            // reset user storage
            $this->objectType->getProcessor()->resetUserStorage([WCF::getUser()->userID]);
        } elseif ($this->userObjectWatch !== null) {
            // unsubscribe
            UserObjectWatchBuilder::delete($this->userObjectWatch);

            // reset user storage
            $this->objectType->getProcessor()->resetUserStorage([WCF::getUser()->userID]);
        }

        return [
            'objectID' => $this->parameters['objectID'],
            'objectType' => $this->parameters['objectType'],
            'subscribe' => $this->parameters['subscribe'],
        ];
    }

    /**
     * Adds a subscription.
     *
     * @return void
     */
    public function subscribe()
    {
        $objectType = ObjectTypeCache::getInstance()->getObjectTypeByName(
            'com.woltlab.wcf.user.objectWatch',
            $this->parameters['data']['objectType']
        );

        UserObjectWatchBuilder::forCreate()
            ->setObjectType($objectType)
            ->setObjectID((int)$this->parameters['data']['objectID'])
            ->setUser(WCF::getUser())
            ->setNotification(!empty($this->parameters['enableNotification']))
            ->create();

        // reset user storage
        $objectType->getProcessor()->resetUserStorage([WCF::getUser()->userID]);
    }

    /**
     * Removes a subscription.
     *
     * @return void
     */
    public function unsubscribe()
    {
        $objectType = ObjectTypeCache::getInstance()->getObjectTypeByName(
            'com.woltlab.wcf.user.objectWatch',
            $this->parameters['data']['objectType']
        );

        if ($this->userObjectWatch !== null) {
            $userObjectWatch = $this->userObjectWatch;
        } else {
            $userObjectWatch = UserObjectWatch::getUserObjectWatch(
                $objectType->objectTypeID,
                WCF::getUser()->userID,
                (int)$this->parameters['data']['objectID']
            );
        }
        UserObjectWatchBuilder::delete($userObjectWatch);

        // reset user storage
        $objectType->getProcessor()->resetUserStorage([WCF::getUser()->userID]);
    }

    /**
     * Validates the subscribe action.
     *
     * @return void
     */
    protected function __validateSubscribe()
    {
        $this->readInteger('objectID', false, 'data');
        $this->readString('objectType', false, 'data');

        // validate object type
        $objectType = ObjectTypeCache::getInstance()->getObjectTypeByName(
            'com.woltlab.wcf.user.objectWatch',
            $this->parameters['data']['objectType']
        );
        if ($objectType === null) {
            throw new UserInputException('objectType');
        }

        // validate object id
        $objectType->getProcessor()->validateObjectID((int)$this->parameters['data']['objectID']);

        // get existing subscription
        $this->userObjectWatch = UserObjectWatch::getUserObjectWatch(
            $objectType->objectTypeID,
            WCF::getUser()->userID,
            (int)$this->parameters['data']['objectID']
        );
    }

    /**
     * Validates the subscribe action.
     *
     * @return void
     */
    public function validateSubscribe()
    {
        $this->__validateSubscribe();

        if ($this->userObjectWatch !== null) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * Validates the unsubscribe action.
     *
     * @return void
     */
    public function validateUnsubscribe()
    {
        $this->__validateSubscribe();

        if ($this->userObjectWatch === null) {
            throw new PermissionDeniedException();
        }
    }
}
