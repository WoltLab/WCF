<?php
namespace wcf\system\form\container;

/**
 * Provides a multiple selection form element container.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.form.container
 * @category	Community Framework
 */
class MultipleSelectionFormElementContainer extends SelectionFormElementContainer {
	/**
	 * container value
	 * @var	array
	 */
	protected $value = [];
	
	/**
	 * Sets container value.
	 * 
	 * @param	array		$value
	 */
	public function setValue(array $value) {
		$this->value = $value;
	}
	
	/**
	 * @see	\wcf\system\form\IFormElementContainer::getHTML()
	 */
	public function getHTML($formName) {
		$content = '';
		foreach ($this->getChildren() as $element) {
			$content .= '<dd>'.$element->getHTML($formName).'</dd>';
		}
		
		return <<<HTML
<section class="section">
	<header class="sectionHeader">
		<h2 class="sectionTitle">{$this->getLabel()}</h2>
		<p class="sectionDescription">{$this->getDescription()}</p>
	</header>
	
	<dl class="wide">
		{$content}
	</dl>
</section>
HTML;
	}
}
