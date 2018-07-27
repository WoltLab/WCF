<?php
namespace wcf\system\form\builder\field\data;
use wcf\system\form\builder\field\IFormField;
use wcf\system\form\builder\IFormDocument;
use wcf\system\form\builder\IFormNode;
use wcf\system\form\builder\IFormParentNode;

/**
 * Default field data processor that maps the form fields to entries in
 * the `data` sub-array with the field ids as array keys and field values
 * as array values.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field\Data
 * @since	3.2
 */
class DefaultFormFieldDataProcessor implements IFormFieldDataProcessor {
	/**
	 * @inheritDoc
	 */
	public function __invoke(IFormDocument $document, array $parameters) {
		$parameters['data'] = [];
		
		$this->getData($document, $parameters['data']);
		
		return $parameters;
	}
	
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
				$data[$node->getObjectProperty()] = $node->getSaveValue();
			}
		}
	}
}
