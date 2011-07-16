<?php
namespace wcf\data\style;
use wcf\data\DatabaseObjectDecorator;
use wcf\system\WCF;
use wcf\util\FileUtil;
use wcf\util\StyleUtil;

/**
 * Represents the active user style.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.style
 * @category 	Community Framework
 */
class ActiveStyle extends DatabaseObjectDecorator {
	/**
	 * @see wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\style\Style';
	
	/**
	 * icon cache
	 * @var	array
	 */
	protected $iconCache = array();
	
	/**
	 * Creates a new ActiveStyle object.
	 * 
	 * @param	Style		$object
	 */
	public function __construct(Style $object) {
		parent::__construct($object);
		
		// calculate page logo path
		if (!empty($this->object->data['variables']['page.logo.image']) && !FileUtil::isURL($this->object->data['variables']['page.logo.image']) && StringUtil::substring($this->object->data['variables']['page.logo.image'], 0, 1) !== '/') {
			$this->object->data['variables']['page.logo.image'] = RELATIVE_WCF_DIR . $this->object->data['variables']['page.logo.image'];
		}
		
		// load icon cache
		WCF::getCache()->addResource('icon-'.PACKAGE_ID.'-'.$this->styleID, WCF_DIR.'cache/cache.icon-'.PACKAGE_ID.'-'.$this->styleID.'.php', 'wcf\system\cache\CacheBuilderIcon');
		$this->iconCache = WCF::getCache()->get('icon-'.PACKAGE_ID.'-'.$this->styleID);
	}
	
	/**
	 * Returns the value of a style variable.
	 * 
	 * @param	string		$name
	 * @return	string		value
	 */
	public function getVariable($name) {
		if (isset($this->object->data['variables'][$name])) return $this->object->data['variables'][$name];
		return '';
	}
	
	/**
	 * Returns the path of an icon.
	 * 
	 * @param	string		$iconName
	 * @return	string
	 */
	public function getIconPath($iconName) {
		if (isset($this->iconCache[$iconName])) return $this->iconCache[$iconName];
		return RELATIVE_WCF_DIR.'icon/'.$iconName;
	}
}
