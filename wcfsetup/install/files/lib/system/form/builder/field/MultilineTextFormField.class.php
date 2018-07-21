<?php
declare(strict_types=1);
namespace wcf\system\form\builder\field;

/**
 * Implementation of a form field for multiline text values.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field
 * @since	3.2
 */
class MultilineTextFormField extends TextFormField {
	/**
	 * number of rows of the textarea 
	 * @var	int
	 */
	protected $__rows = 10;
	
	/**
	 * @inheritDoc
	 */
	protected $templateName = '__multilineTextFormField';
	
	/**
	 * Returns the number of rows of the textarea. If the number of rows has not been
	 * explicitly set, `10` is returned.
	 * 
	 * @return	int	number of textarea rows
	 */
	public function getRows() {
		return $this->__rows;
	}
	
	/**
	 * Sets the number of rows of the textarea and returns this field.
	 * 
	 * @param	int	$rows			number of textarea rows
	 * @return	static				this field
	 * 
	 * @throws	\InvalidArgumentException	if given number of rows is invalid
	 */
	public function rows(int $rows) {
		if ($rows <= 0) {
			throw new \InvalidArgumentException("Given number of rows is not positive.");
		}  
		
		$this->__rows = $rows;
		
		return $this;
	}
}
