<?php
namespace wcf\system\form\element;
use wcf\system\form\container\SingleSelectionFormElementContainer;

/**
 * Provides a radio form element.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Element
 * 
 * @method	SingleSelectionFormElementContainer	getParent()
 */
class SingleSelectionFormElement extends AbstractNamedFormElement {
	/**
	 * @inheritDoc
	 */
	public function getHTML($formName) {
		return <<<HTML
<label><input type="radio" name="{$formName}{$this->getParent()->getName()}" value="{$this->getValue()}"> {$this->getLabel()}</label>
HTML;
	}
}
