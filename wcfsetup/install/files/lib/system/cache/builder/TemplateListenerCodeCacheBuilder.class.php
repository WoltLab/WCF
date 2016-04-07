<?php
namespace wcf\system\cache\builder;
use wcf\data\template\listener\TemplateListenerList;

/**
 * Caches the template listener code for a certain environment.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class TemplateListenerCodeCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @see	\wcf\system\cache\builder\AbstractCacheBuilder::rebuild()
	 */
	public function rebuild(array $parameters) {
		// get template codes for specified template
		$templateListenerList = new TemplateListenerList();
		$templateListenerList->getConditionBuilder()->add("template_listener.environment = ?", array($parameters['environment']));
		$templateListenerList->sqlOrderBy = 'template_listener.niceValue ASC, template_listener.listenerID ASC';
		$templateListenerList->readObjects();
		
		$data = array();
		foreach ($templateListenerList->getObjects() as $templateListener) {
			if (!isset($data[$templateListener->templateName])) {
				$data[$templateListener->templateName] = array();
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
