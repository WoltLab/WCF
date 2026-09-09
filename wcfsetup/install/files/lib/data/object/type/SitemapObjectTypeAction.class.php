<?php

namespace wcf\data\object\type;

use wcf\data\IToggleAction;
use wcf\system\exception\IllegalLinkException;
use wcf\system\registry\RegistryHandler;
use wcf\system\sitemap\SitemapHandler;
use wcf\system\WCF;

/**
 * Executes sitemap object type-related actions.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  ObjectType      create()
 * @method  ObjectTypeEditor[]  getObjects()
 * @method  ObjectTypeEditor    getSingleObject()
 *
 * @deprecated 6.3 Sitemap objects are toggled through the endpoints
 *             `core/sitemaps/{objectName}/enable` and `core/sitemaps/{objectName}/disable`.
 */
class SitemapObjectTypeAction extends ObjectTypeAction implements IToggleAction
{
    /**
     * @inheritDoc
     */
    protected $className = ObjectTypeEditor::class;

    /**
     * @inheritDoc
     */
    protected $requireACP = ['toggle'];

    #[\Override]
    public function toggle()
    {
        foreach ($this->getObjects() as $objectEditor) {
            $sitemapData = RegistryHandler::getInstance()->get(
                'com.woltlab.wcf',
                SitemapHandler::REGISTRY_PREFIX . $objectEditor->objectType
            );
            $sitemapData = @\unserialize($sitemapData);

            if (\is_array($sitemapData)) {
                $sitemapData['isDisabled'] = $sitemapData['isDisabled'] !== 0 ? 0 : 1;
            } else {
                $sitemapData = [
                    'rebuildTime' => $objectEditor->rebuildTime,
                    'isDisabled' => (int)$objectEditor->isDisabled !== 0 ? 0 : 1,
                ];
            }

            RegistryHandler::getInstance()->set(
                'com.woltlab.wcf',
                SitemapHandler::REGISTRY_PREFIX . $objectEditor->objectType,
                \serialize($sitemapData)
            );
        }
    }

    #[\Override]
    public function validateToggle()
    {
        if ($this->objects === []) {
            $this->readObjects();
        }

        WCF::getSession()->checkPermissions(['admin.management.canRebuildData']);

        foreach ($this->getObjects() as $objectEditor) {
            if ($objectEditor->definitionID !== ObjectTypeCache::getInstance()->getDefinitionByName('com.woltlab.wcf.sitemap.object')->definitionID) {
                throw new IllegalLinkException();
            }
        }
    }
}
