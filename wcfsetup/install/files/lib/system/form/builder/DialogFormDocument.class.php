<?php
namespace wcf\system\form\builder;
use wcf\system\form\builder\button\FormButton;

/**
 * Represents a form (document) in a dialog.
 * 
 * By default, the global form error message is now shown for dialog forms and it is assumed that
 * the form is requested via an AJAX request.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder
 * @since	5.2
 */
class DialogFormDocument extends FormDocument {
	/**
	 * @inheritDoc
	 */
	protected $ajax = true;
	
	/**
	 * is `true` if dialog from can be canceled and is `false` otherwise
	 * @var	bool
	 */
	protected $isCancelable = true;
	
	/**
	 * @inheritDoc
	 */
	protected $showErrorMessage = false;
	
	/**
	 * Sets whether the dialog from can be canceled and return this document.
	 * 
	 * For cancelable dialog forms, a cancel button is added.
	 * 
	 * @param	bool	$cancelable	determines if dialog from can be canceled
	 * @return	static			this document
	 */
	public function cancelable($cancelable = true) {
		$this->isCancelable = $cancelable;
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function createDefaultButton() {
		parent::createDefaultButton();
		
		if ($this->isCancelable()) {
			$this->addButton(
				FormButton::create('cancelButton')
					->attribute('data-type', 'cancel')
					->label('wcf.global.button.cancel')
			);
		}
	}
	
	/**
	 * Returns `true` if the dialog from can be canceled and `false` otherwise.
	 * 
	 * If it has not explicitly set whether the dialog form can be canceled,
	 * `true` is returned.
	 * 
	 * @return	bool
	 */
	public function isCancelable() {
		return $this->isCancelable;
	}
}
