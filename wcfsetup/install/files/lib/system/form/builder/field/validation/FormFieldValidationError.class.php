<?php
declare(strict_types=1);
namespace wcf\system\form\builder\field\validation;
use wcf\system\WCF;

/**
 * Represents an error that occured during the validation of a form field.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field\Validation
 * @since	3.2
 */
class FormFieldValidationError implements IFormFieldValidationError {
	/**
	 * additional error information, also used to resolve error message from language item
	 * @var	array
	 */
	protected $information;
	
	/**
	 * language item containing the error message
	 * @var	string
	 */
	protected $languageItem;
	
	/**
	 * error type
	 * @var	string
	 */
	protected $type;
	
	/**
	 * @inheritDoc
	 */
	public function __construct(string $type, string $languageItem = null, array $information = []) {
		if ($languageItem === null) {
			$languageItem = 'wcf.global.form.error.' . $type;
		}
		else if (!is_string($languageItem)) {
			throw new \InvalidArgumentException("Given language item is no string, '" . gettype($languageItem) . "' given.'");
		}
		
		$this->type = $type;
		$this->languageItem = $languageItem;
		$this->information = $information;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getHtml() {
		return WCF::getTPL()->fetch('__formFieldError', 'wcf', [
			'error' => $this
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getInformation() {
		return $this->information;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getMessage() {
		return WCF::getLanguage()->getDynamicVariable($this->languageItem, $this->information);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getType() {
		return $this->type;
	}
}
