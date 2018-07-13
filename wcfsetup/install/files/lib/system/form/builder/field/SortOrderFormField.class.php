<?php
declare(strict_types=1);
namespace wcf\system\form\builder\field;

/**
 * Implementation of a form field to set the sort order.
 * 
 * This field uses the `wcf.global.showOrder` language item as the default
 * form field label and uses `ASC` and `DESC` as the default options.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field
 * @since	3.2
 */
class SortOrderFormField extends SingleSelectionFormField {
	use TDefaultIdFormField;
	
	/**
	 * @inheritDoc
	 */
	public function __construct() {
		$this->label('wcf.global.showOrder');
		$this->options([
			'ASC' => 'wcf.global.sortOrder.ascending',
			'DESC' => 'wcf.global.sortOrder.descending',
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	protected static function getDefaultId(): string {
		return 'sortOrder';
	}
}
