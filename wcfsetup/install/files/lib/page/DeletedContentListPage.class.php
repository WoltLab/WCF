<?php

namespace wcf\page;

use Laminas\Diactoros\Response\RedirectResponse;
use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectList;
use wcf\data\object\type\ObjectTypeCache;
use wcf\event\moderation\DeletedContentProvidersCollecting;
use wcf\system\event\EventHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\moderation\IDeletedContentListViewProvider;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * List of deleted content.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated 6.2 Use `DeletedContentListViewPage` instead.
 *
 * @extends MultipleLinkPage<DatabaseObjectList<DatabaseObject>>
 */
class DeletedContentListPage extends MultipleLinkPage
{
    /**
     * @inheritDoc
     */
    public $loginRequired = true;

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['mod.general.canUseModeration'];

    /**
     * object type object
     * @var \wcf\data\object\type\ObjectType
     */
    public $objectType;

    /**
     * @var array<string, IDeletedContentProvider | IDeletedContentListViewProvider>
     */
    private array $providers = [];

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        $this->loadProviders();

        if (isset($_REQUEST['objectType'])) {
            $this->objectType = ObjectTypeCache::getInstance()->getObjectTypeByName(
                'com.woltlab.wcf.deletedContent',
                $_REQUEST['objectType']
            );

            if ($this->objectType === null) {
                throw new IllegalLinkException();
            }
        } else {
            if ($this->providers === []) {
                throw new IllegalLinkException();
            }

            $provider = \reset($this->providers);
            if ($provider instanceof IDeletedContentListViewProvider) {
                return new RedirectResponse(LinkHandler::getInstance()->getControllerLink(
                    DeletedContentListViewPage::class,
                    ['provider' => $provider->getIdentifier()]
                ));
            }

            $this->objectType = ObjectTypeCache::getInstance()->getObjectTypeByName(
                'com.woltlab.wcf.deletedContent',
                $provider->getIdentifier()
            );
        }
    }

    private function loadProviders(): void
    {
        $event = new DeletedContentProvidersCollecting();
        EventHandler::getInstance()->fire($event);
        $this->providers = $event->getProviders();
    }

    /**
     * @inheritDoc
     */
    protected function initObjectList()
    {
        $this->objectList = $this->objectType->getProcessor()->getObjectList();
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'providerLinks' => $this->getProviderLinks(),
            'objectType' => $this->objectType->objectType,
            'resultListTemplateName' => $this->objectType->getProcessor()->getTemplateName(),
            'resultListApplication' => $this->objectType->getProcessor()->getApplication(),
        ]);
    }

    private function getProviderLinks(): array
    {
        $links = [];
        foreach ($this->providers as $identifier => $provider) {
            if ($provider instanceof IDeletedContentListViewProvider) {
                $title = $provider->getObjectTypeTitle();
                $link = LinkHandler::getInstance()->getControllerLink(DeletedContentListViewPage::class, [
                    'provider' => $identifier,
                ]);
            } else {
                $title = WCF::getLanguage()->getDynamicVariable('wcf.moderation.deletedContent.objectType.' . $identifier);
                $link = LinkHandler::getInstance()->getControllerLink(self::class, [
                    'objectType' => $identifier,
                ]);
            }

            $links[] = [
                'identifier' => $identifier,
                'title' => $title,
                'link' => $link,
            ];
        }

        return $links;
    }
}
