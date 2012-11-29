<?php
namespace wcf\system\cache\builder;
use wcf\data\template\listener\TemplateListenerList;
use wcf\system\package\PackageDependencyHandler;

/**
 * Caches template listener code.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class TemplateListenerCodeCacheBuilder implements ICacheBuilder {
	/**
	 * @see	wcf\system\cache\ICacheBuilder::getData()
	 */
	public function getData(array $cacheResource) {
		list(, $environment, $templateName) = explode('-', $cacheResource['cache']);
		
		// get template codes for specified template
		$templateListenerList = new TemplateListenerList();
		$templateListenerList->getConditionBuilder()->add("template_listener.environment = ?", array($environment));
		$templateListenerList->getConditionBuilder()->add("template_listener.templateName = ?", array($templateName));
		$templateListenerList->getConditionBuilder()->add("template_listener.packageID IN (?)", array(PackageDependencyHandler::getInstance()->getDependencies()));
		$templateListenerList->sqlLimit = 0;
		$templateListenerList->readObjects();
		
		$data = array();
		foreach ($templateListenerList->getObjects() as $templateListener) {
			$data[$templateListener->eventName][] = $templateListener->templateCode;
		}
		
		return $data;
	}
}
