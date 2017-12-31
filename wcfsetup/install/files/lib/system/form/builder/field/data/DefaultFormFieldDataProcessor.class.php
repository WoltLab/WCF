<?php
namespace wcf\system\form\builder\field\data;
use wcf\system\form\builder\field\IFormField;
use wcf\system\form\builder\IFormDocument;
use wcf\system\form\builder\IFormNode;

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
		/** @var IFormNode $node */
		foreach ($document->getIterator() as $node) {
			if ($node instanceof IFormField && $node->hasSaveValue()) {
				$parameters['data'][$node->getId()] = $node->getSaveValue();
			}
		}
		
		return $parameters;
	}
}
