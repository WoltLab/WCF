<?php
declare(strict_types=1);
namespace wcf\system\form\builder\field\data;
use wcf\system\form\builder\field\IFormField;
use wcf\system\form\builder\IFormNode;
use wcf\system\form\builder\IFormParentNode;

/**
 * Form field data processor for gui package installation plugin forms that support
 * the `data-tag` that should be used instead of the id if present.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field\Data
 * @since	3.2
 */
class GuiPackageInstallationPluginFormFieldDataProcessor extends DefaultFormFieldDataProcessor {
	/**
	 * Fetches all data from the given node and stores it in the given array.
	 * 
	 * @param	IFormNode	$node		node whose data will be fetched
	 * @param	array		$data		data storage
	 */
	protected function getData(IFormNode $node, array &$data) {
		if ($node->checkDependencies()) {
			if ($node instanceof IFormParentNode) {
				foreach ($node as $childNode) {
					$this->getData($childNode, $data);
				}
			}
			else if ($node instanceof IFormField && $node->isAvailable() && $node->hasSaveValue()) {
				$data[$node->hasAttribute('data-tag') ? $node->getAttribute('data-tag') : $node->getId()] = $node->getSaveValue();
			}
		}
	}
}
