<?php
namespace wcf\system\cache;
use wcf\data\template\listener\TemplateListenerList;
use wcf\system\package\PackageDependencyHandler;

/**
 * Caches template listener information.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache
 * @category 	Community Framework
 */
class CacheBuilderTemplateListener implements CacheBuilder {
	/**
	 * @see CacheBuilder::getData()
	 */
	public function getData($cacheResource) {
		list($cache, $packageID, $environment) = explode('-', $cacheResource['cache']); 
		
		// get templates for current package id
		$templateListenerList = new TemplateListenerList();
		$templateListenerList->getConditionBuilder()->add("template_listener.environment = ?", array($environment));
		// work-around during setup
		if (PACKAGE_ID) $templateListenerList->getConditionBuilder()->add("template_listener.packageID IN (?)", array(PackageDependencyHandler::getDependencies()));
		$templateListenerList->sqlLimit = 0;
		$templateListenerList->readObjects();
		
		$data = array();
		foreach ($templateListenerList->getObjects() as $templateListener) {
			$data[$templateListener->templateName] = array();
		}
		
		return $data;
	}
}
