<?php

namespace wcf\system\interaction;

use wcf\data\DatabaseObject;
use wcf\system\style\FontAwesomeIcon;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Represents the component of a standalone button for an interaction content menu.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class StandaloneInteractionContextMenuComponent extends InteractionContextMenuComponent
{
    protected readonly string $containerID;

    public function __construct(
        IInteractionProvider $provider,
        protected readonly DatabaseObject $object,
        protected readonly string $redirectUrl,
        protected readonly string $reloadHeaderEndpoint = '',
        protected ?InteractionContextMenuComponentConfiguration $configuration = null,
    ) {
        parent::__construct($provider, $configuration);

        $this->containerID = 'wcf-cm-' . StringUtil::getUUID();
    }

    public function render(): string
    {
        $contextMenuOptions = $this->renderContextMenuOptions($this->object);
        if (!$contextMenuOptions) {
            return '';
        }

        return WCF::getTPL()->render(
            'wcf',
            'shared_standaloneInteractionButton',
            [
                'contextMenuOptions' => $contextMenuOptions,
                'initializationCode' => $this->renderInitialization($this->getContainerID()),
                'containerID' => $this->getContainerID(),
                'providerClassName' => \get_class($this->provider),
                'objectID' => $this->object->getObjectID(),
                'redirectUrl' => $this->redirectUrl,
                'reloadHeaderEndpoint' => $this->reloadHeaderEndpoint,
                'configuration' => $this->configuration,
            ],
        );
    }

    public function getContainerID(): string
    {
        return $this->containerID;
    }

    public static function forContentHeaderButton(
        IInteractionProvider $provider,
        DatabaseObject $object,
        string $redirectUrl,
    ): self {
        return new self(
            $provider,
            $object,
            $redirectUrl,
            configuration: new InteractionContextMenuComponentConfiguration(
                buttonCssClassName: 'button',
            )
        );
    }

    public static function forContentInteractionButton(
        IInteractionProvider $provider,
        DatabaseObject $object,
        string $redirectUrl,
        string $label,
        string $reloadHeaderEndpoint = ''
    ): self {
        return new self(
            $provider,
            $object,
            $redirectUrl,
            $reloadHeaderEndpoint,
            new InteractionContextMenuComponentConfiguration(
                cssClassName: 'contentInteractionButton',
                buttonCssClassName: 'button small',
                label: $label,
                icon: FontAwesomeIcon::fromValues('pencil')
            )
        );
    }
}
