<?php
namespace wcf\system\package\validation;
use wcf\system\package\PackageArchive;
use wcf\system\WCF;

/**
 * Recursively validates the package archive and it's delivered requirements.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.package.validation
 * @category	Community Framework
 */
class PackageValidationArchive implements \RecursiveIterator {
	/**
	 * package archive object
	 * @var	\wcf\system\package\PackageArchive
	 */
	protected $archive = null;
	
	/**
	 * list of direct requirements delivered by this package
	 * @var	array<\wcf\system\package\validation\PackageValidationArchive>
	 */
	protected $children = array();
	
	/**
	 * exception occured during validation
	 * @var	\Exception
	 */
	protected $exception = null;
	
	/**
	 * children pointer
	 * @var	integer
	 */
	private $position = 0;
	
	/**
	 * Creates a new package validation archive instance.
	 * 
	 * @param	string		$archive
	 */
	public function __construct($archive) {
		$this->archive = new PackageArchive($archive);
	}
	
	/**
	 * Validates this package and it's delivered requirements.
	 * 
	 * @return	boolean
	 */
	public function validate() {
		//
		// step 1) try to read archive
		//
		try {
			$this->archive->openArchive();
		}
		catch (\Exception $e) {
			$this->exception = $e;
		}
		
		//
		// step 2) traverse requirements
		//
		die("<pre>".print_r($this->archive->getOpenRequirements(), true));
		
		//
		// step 3) check requirements against virtual package table
		//
		
		/* TODO: do something */
		
		//
		// step 4) check exclusions
		//
		
		/* TODO: do something */
		
		return true;
		
	}
	
	/**
	 * Returns the exception message.
	 * 
	 * @return	string
	 */
	public function getExceptionMessage() {
		if ($this->exception === null) {
			return '';
		}
		
		if ($this->exception instanceof PackageValidationException) {
			return WCF::getLanguage()->getDynamicVariable('wcf.package.validation.errorCode.' . $this->exception->getCode(), $this->exception->getDetails());
		}
		
		return $this->exception->getMessage();
	}
	
	/**
	 * @see	\Iterator::rewind()
	 */
	public function rewind() {
		$this->position = 0;
	}
	
	/**
	 * @see	\Iterator::valid()
	 */
	public function valid() {
		return isset($this->children[$this->position]);
	}
	
	/**
	 * @see	\Iterator::next()
	 */
	public function next() {
		$this->position++;
	}
	
	/**
	 * @see	\Iterator::current()
	 */
	public function current() {
		return $this->children[$this->position];
	}
	
	/**
	 * @see	\Iterator::key()
	 */
	public function key() {
		return $this->position;
	}
	
	/**
	 * @see	\RecursiveIterator::getChildren()
	 */
	public function getChildren() {
		return $this->children[$this->position];
	}
	
	/**
	 * @see	\RecursiveIterator::hasChildren()
	 */
	public function hasChildren() {
		return count($this->children) > 0;
	}
}
