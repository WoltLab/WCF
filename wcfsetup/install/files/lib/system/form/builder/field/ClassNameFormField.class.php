<?php
namespace wcf\system\form\builder\field;
use wcf\data\language\Language;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\SingletonFactory;

/**
 * Implementation of a form field to enter the name of a PHP class.
 * 
 * This field uses the `wcf.form.field.className` language item as the default
 * form field label and uses `className` as the default node id.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field
 * @since	5.2
 */
class ClassNameFormField extends TextFormField {
	use TDefaultIdFormField;
	
	/**
	 * `true` if the entered class must exist
	 * @var	bool
	 */
	protected $classExists = true;
	
	/**
	 * name of the interface the entered class must implement
	 * @var	string
	 */
	protected $implementedInterface = '';
	
	/**
	 * `true` if the entered class must be instantiable
	 * @var	bool
	 */
	protected $instantiable = true;
	
	/**
	 * name of the class the entered class must extend
	 * @var	string
	 */
	protected $parentClass = '';
	
	/**
	 * Creates a new instance of `ClassNameFormField`.
	 */
	public function __construct() {
		parent::__construct();
		
		$this->label('wcf.form.field.className');
	}
	
	/**
	 * Sets whether entered class must exist and returns this field.
	 * 
	 * @param	bool		$classExists	determines if entered class must exist
	 * @return	static				this field
	 */
	public function classExists($classExists = true) {
		$this->classExists = $classExists;
		
		return $this;
	}
	
	/**
	 * Returns `true` if the entered class must exist. By default, `true` is
	 * returned.
	 * 
	 * @return	bool
	 */
	public function getClassExists() {
		return $this->classExists;
	}
	
	/**
	 * Returns class the entered class must extend or an empty if the
	 * entered class does not have to extend any specific class. By default,
	 * an empty is returned.
	 * 
	 * @return	string
	 */
	public function getImplementedInterface() {
		return $this->implementedInterface;
	}
	
	/**
	 * Returns name of the interface the entered class must implement or an
	 * empty if the entered class does not have to implement any specific
	 * interface. By default, an empty is returned.
	 *
	 * @return	string
	 */
	public function getParentClass() {
		return $this->parentClass;
	}
	
	/**
	 * Sets the name of the interface the entered class must implement and returns
	 * this field.
	 * 
	 * If no description has been set yet, `wcf.form.field.className.description.interface`
	 * is automatically used for the description.
	 * 
	 * @param	string		$interface	name of the interface the entered class must implement
	 * @return	static				this field
	 * 
	 * @throws	\InvalidArgumentException	if the entered interface does not exists
	 */
	public function implementedInterface($interface) {
		if (!interface_exists($interface)) {
			throw new \InvalidArgumentException("Interface '{$interface}' does not exist.");
		}
		
		$this->implementedInterface = $interface;
		
		if ($this->getDescription() === null) {
			$this->description(
				'wcf.form.field.className.description.interface',
				['interface' => $this->implementedInterface]
			);
		}
		
		return $this;
	}
	
	/**
	 * Sets whether entered class must be instantiable and returns this field.
	 * 
	 * @param	bool		$instantiable	determines if entered class must be instantiable
	 * @return	static				this field
	 */
	public function instantiable($instantiable = true) {
		$this->instantiable = $instantiable;
		
		return $this;
	}
	
	/**
	 * Returns `true` if the entered class must be instantiable. By default,
	 * `true` is returned.
	 *
	 * @return	bool
	 */
	public function isInstantiable() {
		return $this->instantiable;
	}
	
	/**
	 * Returns the name of the class the entered class must extend.
	 * 
	 * @param	string		$parentClass	name of the class the entered class must extend
	 * @return	static				this field
	 * 
	 * @throws	\InvalidArgumentException	if the entered class does not exists
	 */
	public function parentClass($parentClass) {
		if (!class_exists($parentClass)) {
			throw new \InvalidArgumentException("Class '{$parentClass}' does not exist.");
		}
		
		$this->parentClass = $parentClass;
		
		if ($this->getDescription() === null) {
			$this->description(
				'wcf.form.field.className.description.parentClass',
				['parentClass' => $this->parentClass]
			);
		}
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function validateText($text, Language $language = null) {
		parent::validateText($text, $language);
		
		if (empty($this->getValidationErrors())) {
			if ($text === '' && !$this->isRequired()) {
				return;
			}
			else if (substr($text, 0, 1) === '\\') {
				$this->addValidationError(
					new FormFieldValidationError(
						'leadingBackslash',
						'wcf.form.field.className.error.leadingBackslash',
						['language' => $language]
					)
				);
			}
			else if ($this->getClassExists() && !class_exists($text)) {
				$this->addValidationError(
					new FormFieldValidationError(
						'nonExistent',
						'wcf.form.field.className.error.nonExistent',
						['language' => $language]
					)
				);
			}
			else if ($this->getImplementedInterface() !== '' && !is_subclass_of($text, $this->getImplementedInterface())) {
				$this->addValidationError(
					new FormFieldValidationError(
						'interface',
						'wcf.form.field.className.error.interface',
						[
							'language' => $language,
							'interface' => $this->getImplementedInterface()
						]
					)
				);
			}
			else if ($this->getParentClass() !== '' && !is_subclass_of($text, $this->getParentClass())) {
				$this->addValidationError(
					new FormFieldValidationError(
						'parentClass',
						'wcf.form.field.className.error.parentClass',
						[
							'language' => $language,
							'parentClass' => $this->getParentClass()
						]
					)
				);
			}
			else if ($this->isInstantiable()) {
				$reflection = new \ReflectionClass($text);
				$isSingleton = is_subclass_of($text, SingletonFactory::class);
				
				if ((!$isSingleton && !$reflection->isInstantiable()) || ($isSingleton && $reflection->isAbstract())) {
					$this->addValidationError(
						new FormFieldValidationError(
							'isInstantiable',
							'wcf.form.field.className.error.isInstantiable',
							['language' => $language]
						)
					);
				}
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected static function getDefaultId() {
		return 'className';
	}
}
