<?php
namespace wcf\data;
use wcf\system\exception\SystemException;
use wcf\util\ClassUtil;

/**
 * Abstract class for all processible data holder classes.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data
 * @category	Community Framework
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
	 */
	public function getProcessor() {
		if ($this->processor === null) {
			if ($this->className) {
				if (!class_exists($this->className)) {
					throw new SystemException("Unable to find class '".$this->className."'");
				}
				if (!ClassUtil::isInstanceOf($this->className, static::$processorInterface)) {
					throw new SystemException("'".$this->className."' does not implement '".static::$processorInterface."'");
				}
				
				if (ClassUtil::isInstanceOf($this->className, 'wcf\system\SingletonFactory')) {
					$this->processor = call_user_func(array($this->className, 'getInstance'));
				}
				else {
					if (!ClassUtil::isInstanceOf($this->className, 'wcf\data\IDatabaseObjectProcessor')) {
						throw new SystemException("'".$this->className."' does not implement 'wcf\data\IDatabaseObjectProcessor'");
					}
					
					$this->processor = new $this->className($this);
				}
			}
		}
		
		return $this->processor;
	}
}
