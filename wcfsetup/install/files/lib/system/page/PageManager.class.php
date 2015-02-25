<?php
namespace wcf\system\page;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\application\ApplicationHandler;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Manages the available page object types.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.page
 * @category	Community Framework
 */
class PageManager extends SingletonFactory {
	/**
	 * list of available page object types
	 * @var	array<\wcf\data\object\type\ObjectType>
	 */
	protected $objectTypes = array();
	
	/**
	 * @see	\wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		$this->objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.page');
	}
	
	/**
	 * Returns a list of the available page object types.
	 * 
	 * @return	array<\wcf\data\object\type\ObjectType>
	 */
	public function getObjectTypes() {
		return $this->objectTypes;
	}
	
	/**
	 * Returns an array used to build a page selection. If $application is given,
	 * only pages who belong to the application with the given abbreviation
	 * are returned.
	 * 
	 * @param	string		$application
	 * @return	array<string>
	 */
	public function getSelection($application = null) {
		$objectTypes = $this->objectTypes;
		
		// filter by application
		if ($application !== null) {
			// validate application
			if ($application != 'wcf' && ApplicationHandler::getInstance()->getApplication($application) === null) {
				throw new SystemException("Unknown application with abbreviation '".$application."'");
			}
			
			foreach ($objectTypes as $objectTypeName => $objectType) {
				$classNamePieces = explode('\\', $objectType->className);
				
				if ($classNamePieces[0] != $application) {
					unset($objectTypes[$objectTypeName]);
				}
			}
		}
		
		// filter by options
		foreach ($objectTypes as $objectTypeName => $objectType) {
			if ($objectType->options) {
				$options = explode(',', strtoupper($objectType->options));
				foreach ($options as $option) {
					if (!defined($option) || !constant($option)) {
						unset($objectTypes[$objectTypeName]);
						break;
					}
				}
			}
		}
		
		$selection = array();
		foreach ($objectTypes as $objectType) {
			$categoryName = WCF::getLanguage()->get('wcf.page.category.'.$objectType->categoryname);
			if (!isset($selection[$categoryName])) {
				$selection[$categoryName] = array();
			}
			
			$selection[$categoryName][$objectType->objectTypeID] = WCF::getLanguage()->get('wcf.page.'.$objectType->objectType);
		}
		
		ksort($selection);
		
		foreach ($selection as &$subSelection) {
			asort($subSelection);
		}
		
		return $selection;
	}
}
