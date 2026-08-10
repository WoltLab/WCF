<?php

namespace wcf\acp\form;

use wcf\form\AbstractForm;
use wcf\system\cache\builder\UserOptionCacheBuilder;
use wcf\system\option\user\UserOptionHandler;
use wcf\system\WCF;

/**
 * Provides functions to set the default values of user options.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserOptionSetDefaultsForm extends AbstractForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.userOptionDefaults';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.user.canManageUserOption'];

    /**
     * user option handler
     * @var ?UserOptionHandler
     */
    public $optionHandler;

    /**
     * true to apply change to existing users
     * @var int
     */
    public $applyChangesToExistingUsers = 0;

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        $this->optionHandler = new UserOptionHandler(false, '', 'settings');
        $this->optionHandler->init();
    }

    #[\Override]
    public function readFormParameters()
    {
        parent::readFormParameters();

        $this->optionHandler->readUserInput($_POST);

        if (isset($_POST['applyChangesToExistingUsers'])) {
            $this->applyChangesToExistingUsers = \intval($_POST['applyChangesToExistingUsers']);
        }
    }

    #[\Override]
    public function validate()
    {
        parent::validate();

        $this->optionHandler->validate();
    }

    #[\Override]
    public function save()
    {
        parent::save();

        // get new values
        $saveOptions = $this->optionHandler->save();

        // apply changes
        if ($this->applyChangesToExistingUsers !== 0) {
            $optionIDs = \array_keys($saveOptions);

            // get changed options
            $sql = "SELECT  optionID, defaultValue
                    FROM    wcf1_user_option
                    WHERE   optionID IN (?" . \str_repeat(', ?', \count($optionIDs) - 1) . ")";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute($optionIDs);
            $optionIDs = $optionValues = [];
            while ($row = $statement->fetchArray()) {
                // @phpstan-ignore notEqual.notAllowed (values from the database are always strings)
                if ($row['defaultValue'] != $saveOptions[$row['optionID']]) {
                    $optionIDs[] = $row['optionID'];
                    $optionValues[] = $saveOptions[$row['optionID']];
                }
            }

            if ($optionIDs !== []) {
                $sql = "UPDATE  wcf1_user_option_value
                        SET     userOption" . \implode(' = ?, userOption', $optionIDs) . " = ?";
                $statement = WCF::getDB()->prepare($sql);
                $statement->execute(\array_merge($optionValues));
            }
        }

        // save values
        $sql = "UPDATE  wcf1_user_option
                SET     defaultValue = ?
                WHERE   optionID = ?";
        $statement = WCF::getDB()->prepare($sql);
        foreach ($saveOptions as $optionID => $value) {
            $statement->execute([$value, $optionID]);
        }

        // reset cache
        UserOptionCacheBuilder::getInstance()->reset();
        $this->saved();

        WCF::getTPL()->assign('success', true);
    }

    #[\Override]
    public function readData()
    {
        parent::readData();

        if ($_POST === []) {
            $this->optionHandler->readData();
        }
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'optionTree' => $this->optionHandler->getOptionTree(),
            'applyChangesToExistingUsers' => $this->applyChangesToExistingUsers,
        ]);
    }
}
