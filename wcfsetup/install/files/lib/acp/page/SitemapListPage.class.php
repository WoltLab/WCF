<?php

namespace wcf\acp\page;

use wcf\action\ApiAction;
use wcf\page\AbstractPage;
use wcf\system\request\LinkHandler;
use wcf\system\sitemap\object\RegisteredSitemapObject;
use wcf\system\sitemap\SitemapHandler;
use wcf\system\WCF;

/**
 * Shows a list of sitemap objects.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
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
     * @var array<string, RegisteredSitemapObject>
     * @since 6.3
     */
    public $sitemapObjects = [];

    /**
     * @var mixed[]
     */
    private $sitemapData = [];

    #[\Override]
    public function readData()
    {
        parent::readData();

        $this->sitemapObjects = SitemapHandler::getInstance()->getObjects();

        $apiUrl = LinkHandler::getInstance()->getControllerLink(ApiAction::class, ['id' => 'rpc']);

        foreach ($this->sitemapObjects as $sitemapObject) {
            $this->sitemapData[$sitemapObject->getObjectName()] = [
                'changeFreq' => SitemapHandler::getInstance()->getChangeFreq($sitemapObject),
                'rebuildTime' => SitemapHandler::getInstance()->getRebuildTime($sitemapObject),
                'isDisabled' => SitemapHandler::getInstance()->isDisabled($sitemapObject),
                'enableEndpoint' => \sprintf(
                    '%score/sitemaps/%s/enable',
                    $apiUrl,
                    \rawurlencode($sitemapObject->getObjectName())
                ),
                'disableEndpoint' => \sprintf(
                    '%score/sitemaps/%s/disable',
                    $apiUrl,
                    \rawurlencode($sitemapObject->getObjectName())
                ),
            ];
        }
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'sitemapObjects' => $this->sitemapObjects,
            'sitemapData' => $this->sitemapData,
        ]);
    }
}
