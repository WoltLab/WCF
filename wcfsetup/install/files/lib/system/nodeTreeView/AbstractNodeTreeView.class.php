<?php

namespace wcf\system\nodeTreeView;

use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectDecorator;
use wcf\data\IObjectTreeNode;
use wcf\system\interaction\IInteraction;
use wcf\system\interaction\IInteractionProvider;
use wcf\system\interaction\InteractionContextMenuComponent;
use wcf\system\WCF;

/**
 * Abstract implementation of a node tree view.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
abstract class AbstractNodeTreeView
{
    /**
     * @var IInteraction[]
     */
    private array $quickInteractions = [];

    private ?IInteractionProvider $interactionProvider = null;
    private InteractionContextMenuComponent $interactionContextMenuComponent;
    private string $setPositionsEndpoint = '';

    /**
     * Sets the interaction provider that is used to render the interaction context menu.
     */
    public function setInteractionProvider(IInteractionProvider $provider): void
    {
        $this->interactionProvider = $provider;
    }

    /**
     * Returns the interaction provider of the node tree view.
     */
    public function getInteractionProvider(): ?IInteractionProvider
    {
        return $this->interactionProvider;
    }

    /**
     * @return \RecursiveIteratorIterator<IObjectTreeNode>
     */
    public abstract function getNodes(): \RecursiveIteratorIterator;

    public abstract function getNodeLink(IObjectTreeNode $node): string;

    /**
     * Renders the node tree view and returns the HTML code.
     */
    public function render(): string
    {
        return WCF::getTPL()->render('wcf', 'shared_nodeTreeView', ['view' => $this]);
    }

    /**
     * Renders the items and returns the HTML code.
     */
    public function renderItems(): string
    {
        return WCF::getTPL()->render('wcf', 'shared_nodeTreeViewItems', [
            'view' => $this,
        ]);
    }

    /**
     * Renders a single node and its descendants and returns the HTML code.
     */
    public function renderItem(IObjectTreeNode $node): string
    {
        return WCF::getTPL()->render('wcf', 'shared_nodeTreeViewItem', [
            'view' => $this,
            'node' => $node,
        ]);
    }

    /**
     * Returns the node with the given object id or `null` if no such node exists.
     */
    public function getNode(int $objectID): ?IObjectTreeNode
    {
        foreach ($this->getNodes() as $node) {
            if ($node->getObjectID() == $objectID) {
                return $node;
            }
        }

        return null;
    }

    /**
     * Returns true, if this node tree view is accessible for the active user.
     */
    public function isAccessible(): bool
    {
        return true;
    }

    /**
     * Gets the additional parameters of the node tree view.
     *
     * @return mixed[]
     */
    public function getParameters(): array
    {
        return [];
    }

    /**
     * Returns true, if this node tree view has interactions.
     */
    public function hasInteractions(): bool
    {
        return $this->interactionProvider !== null || $this->quickInteractions !== [];
    }

    /**
     * Adds a quick interaction.
     */
    public function addQuickInteraction(IInteraction $interaction): void
    {
        $this->quickInteractions[] = $interaction;
    }

    /**
     * Returns the quick interactions.
     *
     * @return IInteraction[]
     */
    public function getQuickInteractions(): array
    {
        return $this->quickInteractions;
    }

    /**
     * Renders the quick interactions for the given node.
     */
    public function renderQuickInteractions(IObjectTreeNode $node): string
    {
        if ($node instanceof DatabaseObjectDecorator) {
            $object = $node->getDecoratedObject();
        } else if ($node instanceof DatabaseObject) {
            $object = $node;
        } else {
            throw new \LogicException('node has to be a `DatabaseObject`');
        }

        $availableInteractions = \array_filter(
            $this->getQuickInteractions(),
            static fn($interaction) => $interaction->isAvailable($object)
        );

        return \implode("\n", \array_map(
            static fn($interaction) => $interaction->render($object),
            $availableInteractions
        ));
    }

    /**
     * Renders the interactions for the given node.
     */
    public function renderInteractionContextMenuButton(IObjectTreeNode $node): string
    {
        if ($this->interactionProvider === null) {
            return '';
        }

        if ($node instanceof DatabaseObjectDecorator) {
            $object = $node->getDecoratedObject();
        } else if ($node instanceof DatabaseObject) {
            $object = $node;
        } else {
            throw new \LogicException('node has to be a `DatabaseObject`');
        }

        return $this->getInteractionContextMenuComponent()->renderButton($object);
    }

    /**
     * Returns the view of the interaction context menu.
     */
    public function getInteractionContextMenuComponent(): InteractionContextMenuComponent
    {
        if ($this->interactionProvider === null) {
            throw new \BadMethodCallException("Missing interaction provider.");
        }

        if (!isset($this->interactionContextMenuComponent)) {
            $this->interactionContextMenuComponent = new InteractionContextMenuComponent($this->interactionProvider);
        }

        return $this->interactionContextMenuComponent;
    }

    /**
     * Renders the initialization code for the interactions of the node tree view.
     */
    public function renderInteractionInitialization(): string
    {
        $code = '';
        if ($this->interactionProvider !== null) {
            $code = $this->getInteractionContextMenuComponent()->renderInitialization($this->getID());
        }

        if ($this->quickInteractions !== []) {
            $code .= "\n";
            $code .= \implode("\n", \array_map(
                fn($interaction) => $interaction->renderInitialization($this->getID()),
                $this->getQuickInteractions()
            ));
        }

        return $code;
    }

    /**
     * Returns the name of the node tree view class.
     */
    public function getClassName(): string
    {
        return static::class;
    }

    /**
     * Returns the id of this node tree view.
     */
    public function getID(): string
    {
        $id = \str_replace('\\', '_', static::class);

        if ($this->getParameters() !== []) {
            $parameters = $this->getParameters();
            \array_multisort($parameters);

            $id .= '_' . \sha1(\serialize($parameters));
        }

        return $id;
    }

    public function setSetPositionsEndpoint(string $endpoint): void
    {
        $this->setPositionsEndpoint = $endpoint;
    }

    public function getSetPositionsEndpoint(): string
    {
        return $this->setPositionsEndpoint;
    }
}
