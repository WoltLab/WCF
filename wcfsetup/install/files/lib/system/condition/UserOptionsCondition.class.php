<?php

namespace wcf\system\condition;

use wcf\data\condition\Condition;
use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectList;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\system\exception\InvalidObjectArgument;
use wcf\system\option\user\UserOptionHandler;
use wcf\system\WCF;

/**
 * Condition implementation for the options of a user.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserOptionsCondition extends AbstractMultipleFieldsCondition implements
    IContentCondition,
    IObjectListCondition,
    IUserCondition
{
    use TObjectListUserCondition;

    /**
     * user option handler object
     * @var UserOptionHandler
     */
    protected $optionHandler;

    /**
     * @inheritDoc
     */
    public function __construct(DatabaseObject $object)
    {
        parent::__construct($object);

        $this->optionHandler = new UserOptionHandler(false);
        $this->optionHandler->enableConditionMode();
        $this->optionHandler->init();
    }

    /**
     * @inheritDoc
     */
    public function addObjectListCondition(DatabaseObjectList $objectList, array $conditionData)
    {
        if (!($objectList instanceof UserList)) {
            throw new InvalidObjectArgument($objectList, UserList::class, 'Object list');
        }

        $optionValues = $conditionData['optionValues'];

        foreach ($this->optionHandler->getCategoryOptions('profile') as $option) {
            $option = $option['object'];

            if (isset($optionValues[$option->optionName])) {
                /** @noinspection PhpUndefinedMethodInspection */
                $this->optionHandler->getTypeObject($option->optionType)->addCondition(
                    $objectList,
                    $option,
                    $optionValues[$option->optionName]
                );
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function checkUser(Condition $condition, User $user)
    {
        /** @noinspection PhpUndefinedFieldInspection */
        $optionValues = $condition->optionValues;

        $checkSuccess = true;
        foreach ($this->optionHandler->getCategoryOptions('profile') as $option) {
            $option = $option['object'];

            if (isset($optionValues[$option->optionName])) {
                /** @noinspection PhpUndefinedMethodInspection */
                if (
                    !$this->optionHandler->getTypeObject($option->optionType)->checkUser(
                        $user,
                        $option,
                        $optionValues[$option->optionName]
                    )
                ) {
                    $checkSuccess = false;
                    break;
                }
            }
        }

        return $checkSuccess;
    }

    /**
     * @inheritDoc
     */
    public function getData()
    {
        $optionValues = $this->optionHandler->getOptionValues();

        $data = [];
        foreach ($this->optionHandler->getCategoryOptions('profile') as $option) {
            $option = $option['object'];

            if (isset($optionValues[$option->optionName])) {
                /** @noinspection PhpUndefinedMethodInspection */
                $conditionData = $this->optionHandler
                    ->getTypeObject($option->optionType)
                    ->getConditionData($option, $optionValues[$option->optionName]);
                if ($conditionData !== null) {
                    $data[$option->optionName] = $conditionData;
                }
            }
        }

        if (!empty($data)) {
            return [
                'optionValues' => $data,
            ];
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function getHTML()
    {
        return WCF::getTPL()->fetch('shared_userOptionsCondition', 'wcf', [
            'optionTree' => $this->optionHandler->getOptionTree('profile'),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function readFormParameters()
    {
        $this->optionHandler->readUserInput($_POST);
    }

    /**
     * @inheritDoc
     */
    public function reset()
    {
        $this->optionHandler->setOptionValues([]);
    }

    /**
     * @inheritDoc
     */
    public function setData(Condition $condition)
    {
        $this->optionHandler->setOptionValues($condition->conditionData['optionValues']);
    }

    /**
     * @inheritDoc
     */
    public function showContent(Condition $condition)
    {
        if (!WCF::getUser()->userID) {
            return false;
        }

        return $this->checkUser($condition, WCF::getUser());
    }
}
