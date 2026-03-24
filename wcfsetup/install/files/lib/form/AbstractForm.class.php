<?php

namespace wcf\form;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\page\AbstractPage;
use wcf\system\event\EventHandler;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * This class provides default implementations for the Form interface.
 * This includes the default event listener for a form: readFormParameters, validate, save.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
abstract class AbstractForm extends AbstractPage implements IForm
{
    /**
     * active tab menu item
     * @var string
     */
    public $activeTabMenuItem = '';

    /**
     * name of error field
     * @var string|string[]
     */
    public $errorField = '';

    /**
     * error type
     * @var string|string[]
     */
    public $errorType = '';

    /**
     * database object action
     * @var AbstractDatabaseObjectAction
     * @phpstan-ignore missingType.generics
     */
    public $objectAction;

    /**
     * additional fields
     * @var mixed[]
     */
    public $additionalFields = [];

    #[\Override]
    public function submit()
    {
        // call submit event
        EventHandler::getInstance()->fireAction($this, 'submit');

        $this->maybeSetPsr7Response(
            $this->readFormParameters()
        );
        if ($this->hasPsr7Response()) {
            return;
        }

        try {
            $this->maybeSetPsr7Response(
                $this->validate()
            );
            if ($this->hasPsr7Response()) {
                return;
            }

            // validate() is expected to throw in case of errors, thus if we reach
            // this point the input is correct.

            $this->maybeSetPsr7Response(
                $this->save()
            );
            if ($this->hasPsr7Response()) {
                return;
            }
        } catch (UserInputException $e) {
            $this->errorField = $e->getField();
            $this->errorType = $e->getType();
        }
    }

    #[\Override]
    public function readFormParameters()
    {
        // call readFormParameters event
        EventHandler::getInstance()->fireAction($this, 'readFormParameters');

        if (isset($_POST['activeTabMenuItem'])) {
            $this->activeTabMenuItem = StringUtil::trim($_POST['activeTabMenuItem']);
        }
    }

    #[\Override]
    public function validate()
    {
        // call validate event
        EventHandler::getInstance()->fireAction($this, 'validate');

        $this->validateSecurityToken();
    }

    /**
     * Validates the form security token.
     *
     * @return void
     * @throws UserInputException  if the security token is invalid
     * @since 5.2
     */
    protected function validateSecurityToken()
    {
        if (!isset($_POST['t']) || !WCF::getSession()->checkSecurityToken($_POST['t'])) {
            throw new UserInputException('__securityToken');
        }
    }

    #[\Override]
    public function save()
    {
        // call save event
        EventHandler::getInstance()->fireAction($this, 'save');
    }

    /**
     * Calls the 'saved' event after the successful call of the save method.
     * This functions won't called automatically. You must do this manually, if you inherit AbstractForm.
     *
     * @return void
     */
    protected function saved()
    {
        EventHandler::getInstance()->fireAction($this, 'saved');
    }

    #[\Override]
    public function readData()
    {
        if (!empty($_POST) || !empty($_FILES)) {
            $this->submit();
        }

        parent::readData();
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        // assign default variables
        WCF::getTPL()->assign([
            'activeTabMenuItem' => $this->activeTabMenuItem,
            'errorField' => $this->errorField,
            'errorType' => $this->errorType,
        ]);
    }
}
