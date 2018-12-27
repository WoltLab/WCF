<?php
namespace wcf\system\form\builder\field\user\group\option;
use wcf\system\database\util\PreparedStatementConditionBuilder;
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
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field\User\Group\Option
 * @since	3.2
 */
class UserGroupOptionFormField extends ItemListFormField {
	use TDefaultIdFormField;
	use TPackagesFormField;
	
	/**
	 * Creates a new instance of `OptionsFormField`.
	 */
	public function __construct() {
		$this->label('wcf.form.field.userGroupOption');
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		parent::validate();
		
		if (empty($this->getValidationErrors()) && is_array($this->getValue()) && !empty($this->getValue())) {
			$conditionBuilder = new PreparedStatementConditionBuilder();
			$conditionBuilder->add('optionName IN (?)', [$this->getValue()]);
			if (!empty($this->getPackageIDs())) {
				$conditionBuilder->add('packageID IN (?)', [$this->getPackageIDs()]);
			}
			
			$sql = "SELECT	optionName
				FROM	wcf" . WCF_N . "_user_group_option
				" . $conditionBuilder;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditionBuilder->getParameters());
			$availableOptions = $statement->fetchAll(\PDO::FETCH_COLUMN);
			
			$unknownOptions = array_diff($this->getValue(), $availableOptions);
			
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
	protected static function getDefaultId() {
		return 'permissions';
	}
}
