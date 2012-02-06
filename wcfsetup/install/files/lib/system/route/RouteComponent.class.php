<?php
namespace wcf\system\route;
use wcf\system\Regex;

/**
 * Represents a route component.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.route
 * @category 	Community Framework
 */
class RouteComponent {
	/**
	 * indicates if component is options
	 * @var	boolean
	 */
	public $isOptional = false;
	
	/**
	 * default value
	 * @var	string
	 */
	public $defaultValue = null;
	
	/**
	 * name of the component used in the route schema
	 * @var	string
	 */
	public $name = '';
	
	/**
	 * pattern to validate a given component
	 * @var	string
	 */
	public $pattern = null;
	
	/**
	 * Creates new instance of RouteComponent.
	 * 
	 * @param	string		$name
	 * @param	string		$defaultValue
	 * @param	string		$pattern
	 * @param	boolean		$isOptional
	 */
	public function __construct($name, $defaultValue = null, $pattern = null, $isOptional = false) {
		$this->name = $name;
		$this->defaultValue = $defaultValue;
		$this->pattern = $pattern;
		$this->isOptional = $isOptional;
	}
	
	/**
	 * Returns true, if the given string matches this component.
	 * 
	 * @param	string		$string
	 * @return	boolean
	 */
	public function matches($string) {
		return $this->pattern === null || Regex::compile('^'.$this->pattern.'$')->match($string);
	}
}
