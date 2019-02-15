<?php
namespace wcf\system\form\builder\field\data\processor;
use wcf\system\form\builder\IFormDocument;

/**
 * Field data processor implementation that supports a custom processor callable.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field\Data\Processor
 * @since	5.2
 */
class CustomFormFieldDataProcessor implements IFormFieldDataProcessor {
	/**
	 * processor id primarily used for error messages
	 * @var	string
	 */
	protected $id;
	
	/**
	 * callable processing the data
	 * @var	callable 
	 */
	protected $processor;
	
	/**
	 * Initializes a new CustomFormFieldDataProcessor object.
	 * 
	 * @param	string		$id		processor id primarily used for error messages, does not have to be unique
	 * @param	callable	$processor	processor callable
	 * 
	 * @throws	\InvalidArgumentException	if either id or processor callable are invalid
	 */
	public function __construct($id, callable $processor) {
		if (preg_match('~^[a-z][A-z0-9-]*$~', $id) !== 1) {
			throw new \InvalidArgumentException("Invalid id '{$id}' given.");
		}
		
		$this->id = $id;
		
		// validate processor function
		$parameters = (new \ReflectionFunction($processor))->getParameters();
		if (count($parameters) !== 2) {
			throw new \InvalidArgumentException(
				"The processor function must expect two parameters, instead " . count($parameters) .
				" parameter" . (count($parameters) !== 1 ? 's' : '') . " are expected."
			);
		}
		
		/** @var \ReflectionClass $parameterClass */
		$parameterClass = $parameters[0]->getClass();
		if ($parameterClass === null || ($parameterClass->getName() !== IFormDocument::class && !is_subclass_of($parameterClass->getName(), IFormDocument::class))) {
			throw new \InvalidArgumentException(
				"The processor function's first parameter must be an instance of '" . IFormDocument::class . "', instead " .
				($parameterClass === null ? 'any' : "'" . $parameterClass->getName() . "'") . " parameter is expected."
			);
		}
		if (!$parameters[1]->isArray()) {
			throw new \InvalidArgumentException("The processor function's second parameter must be an array.");
		}
		
		$this->processor = $processor;
	}
	
	/**
	 * @inheritDoc
	 */
	public function __invoke(IFormDocument $document, array $parameters) {
		$parameters = call_user_func($this->processor, $document, $parameters);
		
		if (!is_array($parameters)) {
			throw new \UnexpectedValueException("Field data processor '{$this->id}' does not return an array.");
		}
		
		return $parameters;
	}
}
