<?php
namespace wcf\system\option;
use wcf\data\option\Option;
use wcf\system\cache\builder\ControllerCacheBuilder;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Option type implementation for URL controller replacements.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option
 * @category	Community Framework
 */
class UrlControllerReplacementOptionType extends TextareaOptionType {
	/**
	 * list of known controllers grouped by application
	 * @var	array<array>
	 */
	protected $controllers = null;
	
	/**
	 * @see	\wcf\system\option\IOptionType::getData()
	 */
	public function getData(Option $option, $newValue) {
		return $this->cleanup($newValue);
	}
	
	/**
	 * @see	\wcf\system\option\IOptionType::validate()
	 */
	public function validate(Option $option, $newValue) {
		$newValue = $this->cleanup($newValue);
		if (!empty($newValue)) {
			$lines = explode("\n", $newValue);
			
			$aliases = array();
			$controllers = array();
			for ($i = 0, $length = count($lines); $i < $length; $i++) {
				$line = $lines[$i];
				if (preg_match('~^(?P<controller>[a-z][a-z0-9\-]+)=(?P<alias>[a-z][a-z0-9\-]+)$~', $line, $matches)) {
					// check if there is already a replacement for given controller
					if (in_array($matches['controller'], $controllers)) {
						WCF::getTPL()->assign('urlControllerReplacementError', $matches['controller']);
						throw new UserInputException($option->optionName, 'controllerReplacementDuplicateController', array('controller' => $matches['controller']));
					}
					
					// check if there is already the same alias for a different controller
					if (in_array($matches['alias'], $aliases)) {
						WCF::getTPL()->assign('urlControllerReplacementError', $matches['alias']);
						throw new UserInputException($option->optionName, 'controllerReplacementDuplicateAlias', array('alias' => $matches['alias']));
					}
					
					$aliases[] = $matches['alias'];
					$controllers[] = $matches['controller'];
					
					// check if controller exists
					if (!$this->isKnownController($matches['controller'])) {
						WCF::getTPL()->assign('urlControllerReplacementError', $matches['controller']);
						throw new UserInputException($option->optionName, 'controllerReplacementUnknown', array('controller' => $matches['controller']));
					}
					
					// check if alias collides with an existing controller name
					if ($this->isKnownController($matches['alias'])) {
						WCF::getTPL()->assign('urlControllerReplacementError', $matches['alias']);
						throw new UserInputException($option->optionName, 'controllerReplacementCollision', array('alias' => $matches['alias']));
					}
				}
				else {
					WCF::getTPL()->assign('urlControllerReplacementError', $line);
					throw new UserInputException($option->optionName, 'controllerReplacementInvalidFormat', array('line' => $line));
				}
			}
		}
	}
	
	/**
	 * Cleans up newlines and converts input to lower-case.
	 * 
	 * @param	string		$newValue
	 * @return	string
	 */
	protected function cleanup($newValue) {
		$newValue = StringUtil::unifyNewlines($newValue);
		$newValue = trim($newValue);
		$newValue = preg_replace('~\n+~', "\n", $newValue);
		$newValue = mb_strtolower($newValue);
		
		return $newValue;
	}
	
	/**
	 * Returns true if given controller name is known to the system, used to
	 * prevent aliases colliding with existing ones.
	 * 
	 * @param	string		$controller
	 * @return	boolean
	 */
	protected function isKnownController($controller) {
		if ($this->controllers === null) {
			$this->controllers = ControllerCacheBuilder::getInstance()->getData(array(
				'environment' => 'user'
			));
		}
		
		$controller = str_replace('-', '', $controller);
		foreach ($this->controllers as $types) {
			foreach ($types as $controllers) {
				if (isset($controllers[$controller])) {
					return true;
				}
			}
		}
		
		return false;
	}
}
