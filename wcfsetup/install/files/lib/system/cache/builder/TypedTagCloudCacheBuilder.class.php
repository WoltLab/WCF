<?php
namespace wcf\system\cache\builder;

/**
 * Caches the typed tag cloud.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class TypedTagCloudCacheBuilder extends TagCloudCacheBuilder {
	/**
	 * @see	\wcf\system\cache\builder\AbstractCacheBuilder::rebuild()
	 */
	protected function rebuild(array $parameters) {
		$this->objectTypeIDs = $parameters['objectTypeIDs'];
		$this->languageIDs = $parameters['languageIDs'];
		
		// get tags
		$this->getTags();
		
		return $this->tags;
	}
}
