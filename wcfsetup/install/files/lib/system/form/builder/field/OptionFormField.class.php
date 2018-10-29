<?php
namespace wcf\system\form\builder\field;
use wcf\data\package\PackageCache;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\WCF;

/**
 * Implementation of a form field for options.
 * 
 * This field uses the `wcf.form.field.option` language item as the default
 * form field label and uses `options` as the default node id.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field
 * @since	3.2
 */
class OptionFormField extends ItemListFormField {
	use TDefaultIdFormField;
	
	/**
	 * ids of the packages whose options will be considered
	 * @var	int[]
	 */
	protected $__packageIDs = [];
	
	/**
	 * Creates a new instance of `OptionsFormField`.
	 */
	public function __construct() {
		$this->label('wcf.form.field.option');
	}
	
	/**
	 * Returns the ids of the packages whose options will be considered. An
	 * empty array is returned if all packages are considered.
	 * 
	 * @return	int[]
	 */
	public function getPackageIDs() {
		return $this->__packageIDs;
	}
	
	/**
	 * Sets the ids of the packages whose options will be considered. If an
	 * empty array is given, all packages will be considered.
	 * 
	 * @param	int[]		$packageIDs
	 * @return	static
	 * 
	 * @throws	\InvalidArgumentException	if the given package ids are invalid
	 */
	public function packageIDs(array $packageIDs) {
		foreach ($packageIDs as $packageID) {
			if (PackageCache::getInstance()->getPackage($packageID) === null) {
				throw new \InvalidArgumentException("Unknown package with id '{$packageID}'.");
			}
		}
		
		$this->__packageIDs = $packageIDs;
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		parent::validate();
		
		if (empty($this->getValidationErrors()) && is_array($this->getValue()) && !empty($this->getValue())) {
			// ignore `module_attachment`, see https://github.com/WoltLab/WCF/issues/2531
			$options = $this->getValue();
			if (($index = array_search('module_attachment', $options)) !== false) {
				unset($options[$index]);
			}
			
			if (empty($options)) {
				return;
			}
			
			$conditionBuilder = new PreparedStatementConditionBuilder();
			$conditionBuilder->add('optionName IN (?)', [$options]);
			if (!empty($this->getPackageIDs())) {
				$conditionBuilder->add('packageID IN (?)', [$this->getPackageIDs()]);
			}
			
			$sql = "SELECT	optionName
				FROM	wcf" . WCF_N . "_option
				" . $conditionBuilder;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditionBuilder->getParameters());
			$availableOptions = $statement->fetchAll(\PDO::FETCH_COLUMN);
			
			$unknownOptions = array_diff($options, $availableOptions);
			
			if (!empty($unknownOptions)) {
				$this->addValidationError(
					new FormFieldValidationError(
						'nonExistent',
						'wcf.form.field.option.error.nonExistent',
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
		return 'options';
	}
}
