<?php

namespace wcf\system\interaction;

use wcf\data\DatabaseObject;
use wcf\system\WCF;

class StandaloneInteractionContextMenuView extends InteractionContextMenuView
{
    public function __construct(
        IInteractionProvider $provider,
        protected readonly DatabaseObject $object,
        protected readonly string $redirectUrl
    ) {
        parent::__construct($provider);
    }

    public function render(): string
    {
        return WCF::getTPL()->fetch(
            'shared_standaloneInteractionButton',
            'wcf',
            [
                'contextMenuOptions' => $this->renderContextMenuOptions($this->object),
                'initializationCode' => $this->renderInitialization($this->getContainerID()),
                'containerID' => $this->getContainerID(),
                'providerClassName' => \get_class($this->provider),
                'objectID' => $this->object->getObjectID(),
                'redirectUrl' => $this->redirectUrl,
            ],
            true
        );

        return '';
    }

    public function getContainerID(): string
    {
        $classNamePieces = \explode('\\', \get_class($this->object));

        return \implode('-', $classNamePieces) . '-' . $this->object->getObjectID();
    }
}
