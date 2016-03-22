<?php
namespace wcf\system\form\container;

/**
 * Provides a group form element container.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.form
 * @category	Community Framework
 */
class GroupFormElementContainer extends AbstractFormElementContainer {
	/**
	 * @see	\wcf\system\form\IFormElementContainer::getHTML()
	 */
	public function getHTML($formName) {
		$content = '';
		foreach ($this->children as $element) {
			$content .= $element->getHTML($formName);
		}
		
		return <<<HTML
<section class="section">
	<header class="sectionHeader">
		<h2 class="sectionTitle">{$this->getLabel()}</h2>
		<small class="sectionDescription">{$this->getDescription()}</small>
	</header>
	
	{$content}
</section>
HTML;
	}
}
