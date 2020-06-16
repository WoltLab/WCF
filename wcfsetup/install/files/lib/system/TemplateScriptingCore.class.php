<?php
namespace wcf\system;

/**
 * Decorates `WCF` and is used in enterprise mode for non-owners in templates so that access to the
 * database and the template is blocked.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System
 * @since	5.2
 */
class TemplateScriptingCore {
	/**
	 * decorated `WCF` object
	 * @var	WCF 
	 */
	private $wcf;
	
	/**
	 * Initializes a new `TemplateScriptingCore` object.
	 * 
	 * @param	WCF	$wcf
	 */
	public function __construct(WCF $wcf) {
		$this->wcf = $wcf;
	}
	
	/**
	 * Forwards method calls to the decorated `WCF` object but blocks access to the database
	 * and the template object.
	 * 
	 * @param	string	$name		called method
	 * @param	array	$arguments	method parameters
	 * @return	mixed
	 */
	public function __call($name, array $arguments) {
		if (strcasecmp($name, 'getDB') === 0 || strcasecmp($name, 'getTPL') === 0) {
			throw new \BadMethodCallException("'WCF::{$name}()' cannot be called from templates.");
		}
		
		return $this->wcf->$name(...$arguments);
	}

	/**
	 * Forwards property access to the decorated `WCF` object but blocks access to the database
	 * and the template object.
	 * 
	 * @param	string	$name	accessed property
	 * @return	mixed
	 */
	public function __get($name) {
		if (strcasecmp($name, 'DB') === 0 || strcasecmp($name, 'TPL') === 0) {
			throw new \BadMethodCallException("'WCF::{$name}' cannot be accessed from templates.");
		}
		
		return $this->wcf->$name;
	}
}
