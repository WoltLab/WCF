<?php
namespace wcf\data;
use wcf\system\exception\ImplementationException;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;

/**
 * Abstract class for all processible data holder classes.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data
 * 
 * @property-read	string|null	$className
 */
class ProcessibleDatabaseObject extends DatabaseObject {
	/**
	 * name of the interface the processor of this database object should implement
	 * @var	string
	 */
	protected static $processorInterface = '';
	
	/**
	 * processor this database object
	 * @var	object
	 */
	protected $processor = null;
	
	/**
	 * Returns the processor this database object.
	 * 
	 * @return	object
	 * @throws	SystemException
	 */
	public function getProcessor() {
		if ($this->processor === null) {
			if ($this->className) {
				if (!class_exists($this->className)) {
					throw new SystemException("Unable to find class '".$this->className."'");
				}
				if (!is_subclass_of($this->className, static::$processorInterface)) {
					throw new ImplementationException($this->className, static::$processorInterface);
				}
				
				if (is_subclass_of($this->className, SingletonFactory::class)) {
					$this->processor = call_user_func([$this->className, 'getInstance']);
				}
				else {
					if (!is_subclass_of($this->className, IDatabaseObjectProcessor::class)) {
						throw new ImplementationException($this->className, IDatabaseObjectProcessor::class);
					}
					
					$this->processor = new $this->className($this);
				}
			}
		}
		
		return $this->processor;
	}
}
