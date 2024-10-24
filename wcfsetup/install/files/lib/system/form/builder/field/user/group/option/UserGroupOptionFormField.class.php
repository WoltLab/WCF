<?php

namespace wcf\system\form\builder\field\user\group\option;

use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\form\builder\field\IPackagesFormField;
use wcf\system\form\builder\field\ItemListFormField;
use wcf\system\form\builder\field\TDefaultIdFormField;
use wcf\system\form\builder\field\TPackagesFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\WCF;

/**
 * Implementation of a form field for user group options/permissions.
 *
 * This field uses the `wcf.form.field.userGroupOption` language item as the default
 * form field label and uses `permissions` as the default node id.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
final class UserGroupOptionFormField extends ItemListFormField implements IPackagesFormField
{
    use TDefaultIdFormField;
    use TPackagesFormField;

    /**
     * Creates a new instance of `OptionsFormField`.
     */
    public function __construct()
    {
        parent::__construct();

        $this->label('wcf.form.field.userGroupOption');
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        parent::validate();

        if (empty($this->getValidationErrors()) && \is_array($this->getValue()) && !empty($this->getValue())) {
            $conditionBuilder = new PreparedStatementConditionBuilder();
            $conditionBuilder->add('optionName IN (?)', [$this->getValue()]);
            if (!empty($this->getPackageIDs())) {
                $conditionBuilder->add('packageID IN (?)', [$this->getPackageIDs()]);
            }

            $sql = "SELECT  optionName
                    FROM    wcf1_user_group_option
                    " . $conditionBuilder;
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute($conditionBuilder->getParameters());
            $availableOptions = $statement->fetchAll(\PDO::FETCH_COLUMN);

            $unknownOptions = \array_diff($this->getValue(), $availableOptions);

            if (!empty($unknownOptions)) {
                $this->addValidationError(
                    new FormFieldValidationError(
                        'nonExistent',
                        'wcf.form.field.userGroupOption.error.nonExistent',
                        ['options' => $unknownOptions]
                    )
                );
            }
        }
    }

    /**
     * @inheritDoc
     */
    protected static function getDefaultId()
    {
        return 'permissions';
    }
}
