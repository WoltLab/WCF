<?php
namespace wcf\system\cache\builder;
use wcf\data\template\listener\TemplateListenerList;

/**
 * Caches template listener information.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 * @deprecated	since 2.1
 */
class TemplateListenerCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @see	\wcf\system\cache\builder\AbstractCacheBuilder::rebuild()
	 */
	public function rebuild(array $parameters) {
		// get templates for current package id
		$templateListenerList = new TemplateListenerList();
		$templateListenerList->getConditionBuilder()->add("template_listener.environment = ?", array($parameters['environment']));
		$templateListenerList->sqlOrderBy = "template_listener.listenerID ASC";
		$templateListenerList->readObjects();
		
		$data = array();
		foreach ($templateListenerList->getObjects() as $templateListener) {
			$data[$templateListener->templateName] = array();
		}
		
		return $data;
	}
}
