<?php
namespace wcf\system\form\builder\field\dependency;

/**
 * Represents a dependency that requires the value of a field not to be empty.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field\Dependency
 * @since	5.2
 */
class NonEmptyFormFieldDependency extends AbstractFormFieldDependency {
	/**
	 * @inheritDoc
	 */
	protected $templateName = '__nonEmptyFormFieldDependency';
	
	/**
	 * @inheritDoc
	 */
	public function checkDependency() {
		return !empty($this->getField()->getValue());
	}
}
