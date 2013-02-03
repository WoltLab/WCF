<?php
namespace wcf\system\cache\builder;
use wcf\data\template\listener\TemplateListenerList;

/**
 * Caches the template listener code for a certain template in a certain environment.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class TemplateListenerCodeCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @see	wcf\system\cache\builder\AbstractCacheBuilder::rebuild()
	 */
	public function rebuild(array $parameters) {
		// get template codes for specified template
		$templateListenerList = new TemplateListenerList();
		$templateListenerList->getConditionBuilder()->add("template_listener.environment = ?", array($parameters['environment']));
		$templateListenerList->getConditionBuilder()->add("template_listener.templateName = ?", array($parameters['templateName']));
		$templateListenerList->readObjects();
		
		$data = array();
		foreach ($templateListenerList->getObjects() as $templateListener) {
			$data[$templateListener->eventName][] = $templateListener->templateCode;
		}
		
		return $data;
	}
}
