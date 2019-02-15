<?php
namespace wcf\system\form\builder\data;
use wcf\system\form\builder\field\data\processor\IFormFieldDataProcessor;
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
	 * @var	IFormFieldDataProcessor[]
	 */
	protected $processors = [];
	
	/**
	 * @inheritDoc
	 */
	public function add(IFormFieldDataProcessor $processor) {
		$this->processors[] = $processor;
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getData(IFormDocument $document) {
		$parameters = [];
		foreach ($this->processors as $processor) {
			$parameters = $processor($document, $parameters);
		}
		
		return $parameters;
	}
}
