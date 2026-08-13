<?php

namespace wcf\page;

use wcf\system\exception\PermissionDeniedException;
use wcf\system\request\LinkHandler;
use wcf\system\nodeTreeView\AbstractNodeTreeView;
use wcf\system\WCF;

/**
 * Abstract implementation of a page that is rendering a node tree view.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @template TNodeTreeView of AbstractNodeTreeView
 */
abstract class AbstractNodeTreeViewPage extends AbstractPage
{
    /**
     * @var TNodeTreeView
     */
    protected AbstractNodeTreeView $nodeTreeView;

    #[\Override]
    public function show()
    {
        $this->canonicalURL = $this->getCanonicalUrl();

        parent::show();
    }

    #[\Override]
    public function readData()
    {
        parent::readData();

        $this->initNodeTreeView();
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'nodeTreeView' => $this->nodeTreeView,
        ]);
    }

    protected function initNodeTreeView(): void
    {
        $this->nodeTreeView = $this->createNodeTreeView();
        if (!$this->nodeTreeView->isAccessible()) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function getBaseUrlParameters(): array
    {
        return [];
    }

    protected function getCanonicalUrl(): string
    {
        return LinkHandler::getInstance()->getControllerLink(
            static::class,
            $this->getBaseUrlParameters()
        );
    }

    /**
     * Returns the node tree view instance for the rendering of this page.
     *
     * @return TNodeTreeView
     */
    protected abstract function createNodeTreeView(): AbstractNodeTreeView;
}
