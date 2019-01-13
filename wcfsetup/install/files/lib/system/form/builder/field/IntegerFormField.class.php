<?php
namespace wcf\system\form\builder\field;

/**
 * Implementation of a form field for integer values.
 * 
 * If a non-required integer field is left empty, its value is `0`.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field
 * @since	5.2
 */
class IntegerFormField extends AbstractNumericFormField {
	/**
	 * @inheritDoc
	 */
	protected $integerValues = true;
}
