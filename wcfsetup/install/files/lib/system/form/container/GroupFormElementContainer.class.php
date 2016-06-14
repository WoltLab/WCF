<?php
namespace wcf\system\form\container;

/**
 * Provides a group form element container.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form
 */
class GroupFormElementContainer extends AbstractFormElementContainer {
	/**
	 * @inheritDoc
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
		<p class="sectionDescription">{$this->getDescription()}</p>
	</header>
	
	{$content}
</section>
HTML;
	}
}
