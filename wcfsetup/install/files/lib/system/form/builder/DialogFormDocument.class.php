<?php
namespace wcf\system\form\builder;
use wcf\system\WCF;

/**
 * Represents a form (document) in a dialog.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder
 * @since	5.2
 */
class DialogFormDocument extends FormDocument {
	/**
	 * is `true` if dialog from can be canceled and is `false` otherwise
	 * @var	bool
	 */
	protected $__isCancelable = true;
	
	/**
	 * Sets whether the dialog from can be canceled and return this document.
	 * 
	 * @param	bool	$cancelable	determines if dialog from can be canceled
	 * @return	static			this document
	 */
	public function cancelable($cancelable = true) {
		$this->__isCancelable = $cancelable;
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getAction() {
		// do not throw exception if no action has been set as a dialog
		// form does not require an action to be set
		if ($this->__action === null) {
			$this->__action = '';
		}
		
		return $this->__action;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getHtml() {
		return WCF::getTPL()->fetch(
			'__dialogForm',
			'wcf',
			array_merge($this->getHtmlVariables(), ['form' => $this])
		);
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
		return $this->__isCancelable;
	}
}
