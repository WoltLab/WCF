<?php
namespace wcf\system\cache\builder;
use wcf\data\template\listener\TemplateListenerList;

/**
 * Caches template listener information.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cache\Builder
 * @deprecated	2.1
 */
class TemplateListenerCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @inheritDoc
	 */
	public function rebuild(array $parameters) {
		// get templates for current package id
		$templateListenerList = new TemplateListenerList();
		$templateListenerList->getConditionBuilder()->add("template_listener.environment = ?", [$parameters['environment']]);
		$templateListenerList->sqlOrderBy = "template_listener.listenerID ASC";
		$templateListenerList->readObjects();
		
		$data = [];
		foreach ($templateListenerList->getObjects() as $templateListener) {
			$data[$templateListener->templateName] = [];
		}
		
		return $data;
	}
}
