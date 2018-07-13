<?php
declare(strict_types=1);
namespace wcf\system\form\builder\field;

/**
 * Implementation of a form field for an object title.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field
 * @since	3.2
 */
class TitleFormField extends TextFormField {
	use TDefaultIdFormField;
	
	/**
	 * Creates a new instance of `TitleFormField`.
	 */
	public function __construct() {
		$this->label('wcf.global.title');
	}
	
	/**
	 * @inheritDoc
	 */
	protected static function getDefaultId(): string {
		return 'title';
	}
}
