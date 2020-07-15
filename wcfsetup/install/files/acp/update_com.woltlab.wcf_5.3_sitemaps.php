<?php
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\registry\RegistryHandler;
use wcf\system\worker\SitemapRebuildWorker;

/**
 * @author	Joshua Ruesweg
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core
 */

// HEADS UP: This script must be executed, BEFORE the objectType-PIP is executed.  

$sitemapObjects = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.sitemap.object');

foreach ($sitemapObjects as $sitemapObject) {
	RegistryHandler::getInstance()->set('com.woltlab.wcf', SitemapRebuildWorker::REGISTRY_PREFIX . $sitemapObject->objectType, serialize([
		'priority' => $sitemapObject->priority,
		'changeFreq' => $sitemapObject->changeFreq,
		'rebuildTime' => $sitemapObject->rebuildTime,
		'isDisabled' => $sitemapObject->isDisabled,
	]));
}
