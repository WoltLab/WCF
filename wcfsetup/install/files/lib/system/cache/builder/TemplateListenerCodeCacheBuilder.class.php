<?php
namespace wcf\system\cache\builder;
use wcf\data\template\listener\TemplateListenerList;

/**
 * Caches the template listener code for a certain environment.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cache\Builder
 */
class TemplateListenerCodeCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @inheritDoc
	 */
	public function rebuild(array $parameters) {
		// get template codes for specified template
		$templateListenerList = new TemplateListenerList();
		$templateListenerList->getConditionBuilder()->add("template_listener.environment = ?", [$parameters['environment']]);
		$templateListenerList->sqlOrderBy = 'template_listener.niceValue ASC, template_listener.listenerID ASC';
		$templateListenerList->readObjects();
		
		$data = [];
		foreach ($templateListenerList->getObjects() as $templateListener) {
			if (!isset($data[$templateListener->templateName])) {
				$data[$templateListener->templateName] = [];
			}
			
			$templateCode = $templateListener->templateCode;
			// wrap template listener code in if condition for options
			// and permissions check
			if ($templateListener->options || $templateListener->permissions) {
				$templateCode = '{if ';
				
				$options = $permissions = [];
				if ($templateListener->options) {
					$options = explode(',', strtoupper($templateListener->options));
					
					$options = array_map(function($value) {
						return "('".$value."'|defined && '".$value."'|constant)";
					}, $options);
					
					$templateCode .= '('.implode(' || ', $options).')';
				}
				if ($templateListener->permissions) {
					$permissions = explode(',', $templateListener->permissions);
					
					$permissions = array_map(function($value) {
						return "\$__wcf->session->getPermission('".$value."')";
					}, $permissions);
					
					if (!empty($options)) {
						$templateCode .= " && ";
					}
					
					$templateCode .= '('.implode(' || ', $permissions).')';
				}
				
				$templateCode .= '}'.$templateListener->templateCode.'{/if}';
			}
			
			$data[$templateListener->templateName][$templateListener->eventName][] = $templateCode;
		}
		
		return $data;
	}
}
