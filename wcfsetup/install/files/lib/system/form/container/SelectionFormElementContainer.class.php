<?php
namespace wcf\system\form\container;
use wcf\util\StringUtil;

/**
 * Basic implementation for form selection element containers.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Container
 */
abstract class SelectionFormElementContainer extends AbstractFormElementContainer {
	/**
	 * container name
	 * @var	string
	 */
	protected $name = '';
	
	/**
	 * Sets container name.
	 * 
	 * @param	string		$name
	 */
	public function setName($name) {
		$this->name = StringUtil::trim($name);
	}
	
	/**
	 * Returns container name
	 * 
	 * @return	string
	 */
	public function getName() {
		return $this->name;
	}
}
