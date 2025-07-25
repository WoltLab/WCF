<?php

namespace wcf\page;

use CuyZ\Valinor\Mapper\MappingError;
use Laminas\Diactoros\Response\RedirectResponse;
use wcf\event\moderation\DeletedContentProvidersCollecting;
use wcf\http\Helper;
use wcf\system\event\EventHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\listView\AbstractListView;
use wcf\system\moderation\IDeletedContentListViewProvider;
use wcf\system\moderation\IDeletedContentProvider;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * List of deleted content using a list view.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 *
 * @template TListView of AbstractListView
 * @extends AbstractListViewPage<TListView>
 */
final class DeletedContentListViewPage extends AbstractListViewPage
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
     * @var array<string, IDeletedContentProvider | IDeletedContentListViewProvider>
     * @phpstan-ignore missingType.generics, missingType.generics
     */
    private array $providers = [];

    private string $providerID;

    /**
     * @var IDeletedContentListViewProvider<TListView>
     */
    private IDeletedContentListViewProvider $provider;

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        $this->loadProviders();

        if ($this->providers === []) {
            throw new IllegalLinkException();
        }

        try {
            $queryParameters = Helper::mapQueryParameters(
                $_GET,
                <<<'EOT'
                    array {
                        provider?: non-empty-string,
                    }
                    EOT
            );
        } catch (MappingError) {
            throw new IllegalLinkException();
        }

        if (!isset($queryParameters['provider'])) {
            $this->providerID = \array_key_first($this->providers);
        } else {
            $this->providerID = $queryParameters['provider'];
            if (!isset($this->providers[$this->providerID])) {
                throw new IllegalLinkException();
            }
        }

        if (!($this->providers[$this->providerID] instanceof IDeletedContentListViewProvider)) {
            return new RedirectResponse(LinkHandler::getInstance()->getControllerLink(DeletedContentListPage::class, [
                'objectType' => $this->providerID,
            ]));
        }

        $this->provider = $this->providers[$this->providerID];
    }

    private function loadProviders(): void
    {
        $event = new DeletedContentProvidersCollecting();
        EventHandler::getInstance()->fire($event);
        $this->providers = $event->getProviders();
    }

    #[\Override]
    protected function createListView(): AbstractListView
    {
        return $this->provider->getListView();
    }

    #[\Override]
    protected function getBaseUrlParameters(): array
    {
        return [
            'provider' => $this->providerID,
        ];
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'contentTitle' => $this->provider->getContentTitle(),
            'providerLinks' => $this->getProviderLinks(),
            'provider' => $this->providerID,
        ]);
    }

    /**
     * @return list<array{
     *  identifier: string,
     *  title: string,
     *  link: string,
     * }>
     */
    private function getProviderLinks(): array
    {
        $links = [];
        foreach ($this->providers as $identifier => $provider) {
            if ($provider instanceof IDeletedContentListViewProvider) {
                $title = $provider->getObjectTypeTitle();
                $link = LinkHandler::getInstance()->getControllerLink(self::class, [
                    'provider' => $identifier,
                ]);
            } else {
                $title = WCF::getLanguage()->getDynamicVariable('wcf.moderation.deletedContent.objectType.' . $identifier);
                $link = LinkHandler::getInstance()->getControllerLink(DeletedContentListPage::class, [
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
