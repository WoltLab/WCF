<?php
namespace wcf\system\cache\builder;
use wcf\data\bbcode\media\provider\BBCodeMediaProviderList;

/**
 * Caches the BBCode media providers.
 * 
 * @author	Tim Duesterhus
 * @copyright	2011-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class BBCodeMediaProviderCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @see	\wcf\system\cache\builder\AbstractCacheBuilder::rebuild()
	 */
	protected function rebuild(array $parameters) {
		$providerList = new BBCodeMediaProviderList();
		$providerList->readObjects();
		
		return $providerList->getObjects();
	}
}
