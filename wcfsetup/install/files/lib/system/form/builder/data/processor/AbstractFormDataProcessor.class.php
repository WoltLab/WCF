<?php
namespace wcf\system\form\builder\data\processor;
use wcf\data\IStorableObject;
use wcf\system\form\builder\IFormDocument;

/**
 * Abstract implementation of a form field data processor that provides default implementations
 * of the required methods.
 * 
 * Instead of implementing `IFormFieldDataProcessor` directly, this abstract class should be extended.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Data\Processor
 * @since	5.2
 */
abstract class AbstractFormDataProcessor implements IFormDataProcessor {
	/**
	 * @inheritDoc
	 */
	public function processFormData(IFormDocument $document, array $parameters) {
		return $parameters;
	}
	
	/**
	 * @inheritDoc
	 */
	public function processObjectData(IFormDocument $document, array $data, IStorableObject $object) {
		return $data;
	}
}
