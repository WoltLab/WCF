<?php
namespace wcf\system\form\container;
use wcf\util\StringUtil;

/**
 * Provides a single selection form element container.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Container
 */
class SingleSelectionFormElementContainer extends SelectionFormElementContainer {
	/**
	 * container value
	 * @var	string
	 */
	protected $value = '';
	
	/**
	 * Sets container value.
	 * 
	 * @param	string		$value
	 */
	public function setValue($value) {
		$this->value = StringUtil::trim($value);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getValue($key) {
		return $this->value;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getHTML($formName) {
		$content = '';
		foreach ($this->getChildren() as $element) {
			$content .= $element->getHTML($formName);
		}
		
		return <<<HTML
<section class="section">
	<header class="sectionHeader">
		<h2 class="sectionTitle">{$this->getLabel()}</h2>
		<p class="sectionDescription">{$this->getDescription()}</p>
	</header>
	
	<dl>
		<dd>{$content}</dd>
	</dl>
</section>
HTML;
	}
}
