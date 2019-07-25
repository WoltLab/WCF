<?php
namespace wcf\system\form\builder\data;
use wcf\data\IStorableObject;
use wcf\system\form\builder\data\processor\CustomFormDataProcessor;
use wcf\system\form\builder\data\processor\IFormDataProcessor;
use wcf\system\form\builder\IFormDocument;

/**
 * Data processor implementation for form fields that populates or manipulates the
 * parameters array passed to the constructor of a database object action.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Data
 * @since	5.2
 */
class FormDataHandler implements IFormDataHandler {
	/**
	 * field data processors
	 * @var	IFormDataProcessor[]
	 */
	protected $processors = [];
	
	/**
	 * @inheritDoc
	 */
	public function addProcessor(IFormDataProcessor $processor) {
		$this->processors[] = $processor;
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getFormData(IFormDocument $document) {
		$parameters = [];
		foreach ($this->processors as $processor) {
			$parameters = $processor->processFormData($document, $parameters);
			
			if (!is_array($parameters)) {
				if ($processor instanceof CustomFormDataProcessor) {
					throw new \UnexpectedValueException("Custom data processor '{$processor->getId()}' does not return an array when processing form data.");
				}
				else {
					throw new \UnexpectedValueException("Data processor '" . get_class($processor) . "' does not return an array when processing form data.");
				}
			}
		}
		
		return $parameters;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getObjectData(IFormDocument $document, IStorableObject $object) {
		$data = $object->getData();
		$objectId = $object->{$object::getDatabaseTableIndexName()};
		foreach ($this->processors as $processor) {
			$data = $processor->processObjectData($document, $data, $objectId);
			
			if (!is_array($data)) {
				if ($processor instanceof CustomFormDataProcessor) {
					throw new \UnexpectedValueException("Custom data processor '{$processor->getId()}' does not return an array when processing object data.");
				}
				else {
					throw new \UnexpectedValueException("Data processor '" . get_class($processor) . "' does not return an array when processing object data.");
				}
			}
		}
		
		return $data;
	}
}
