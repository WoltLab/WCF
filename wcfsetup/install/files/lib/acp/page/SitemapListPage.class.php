<?php

namespace wcf\acp\page;

use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\page\AbstractPage;
use wcf\system\registry\RegistryHandler;
use wcf\system\WCF;
use wcf\system\worker\SitemapRebuildWorker;

/**
 * Shows a list of sitemap object types.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.1
 */
class SitemapListPage extends AbstractPage
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.maintenance.sitemap';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.management.canRebuildData'];

    /**
     * @var array<ObjectType>
     */
    public $sitemapObjectTypes = [];

    /**
     * @var mixed[]
     */
    private $sitemapData = [];

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        $this->sitemapObjectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.sitemap.object');

        foreach ($this->sitemapObjectTypes as $sitemapObjectType) {
            $this->sitemapData[$sitemapObjectType->objectType] = [
                'changeFreq' => $sitemapObjectType->changeFreq,
                'rebuildTime' => $sitemapObjectType->rebuildTime,
                'isDisabled' => 0,
            ];

            $sitemapData = RegistryHandler::getInstance()->get(
                'com.woltlab.wcf',
                SitemapRebuildWorker::REGISTRY_PREFIX . $sitemapObjectType->objectType
            );

            if ($sitemapData !== null) {
                $sitemapData = @\unserialize($sitemapData);

                if (\is_array($sitemapData)) {
                    $this->sitemapData[$sitemapObjectType->objectType]['changeFreq'] = $sitemapData['changeFreq'];
                    $this->sitemapData[$sitemapObjectType->objectType]['rebuildTime'] = $sitemapData['rebuildTime'];
                    $this->sitemapData[$sitemapObjectType->objectType]['isDisabled'] = $sitemapData['isDisabled'];
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'sitemapObjectTypes' => $this->sitemapObjectTypes,
            'sitemapData' => $this->sitemapData,
        ]);
    }
}
