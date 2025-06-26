<?php

namespace wcf\system\view\component;

use wcf\system\WCF;

/**
 * Represents the component of the tabs shown in the content interaction section.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class ContentInteractionTabsComponent
{
    /**
     * @var list<ContentInteractionTab>
     */
    private array $tabs = [];

    public function addTab(string $title, string $link, bool $active = false): void
    {
        if ($active && $this->getActiveTab() !== null) {
            throw new \BadMethodCallException("The tab '{$this->getActiveTab()->link}' is already marked as active");
        }

        $this->tabs[] = new ContentInteractionTab($title, $link, $active);
    }

    private function getActiveTab(): ?ContentInteractionTab
    {
        return \array_find($this->tabs, static fn($tab) => $tab->active);
    }

    public function render(): string
    {
        if (!$this->tabs === []) {
            return '';
        }

        return WCF::getTPL()->render(
            'wcf',
            'shared_contentInteractionTabs',
            [
                'tabs' => $this->tabs,
            ],
        );
    }

    public function countTabs(): int
    {
        return \count($this->tabs);
    }
}

/** @internal */
final class ContentInteractionTab
{
    public function __construct(
        public readonly string $title,
        public readonly string $link,
        public readonly bool $active,
    ) {}
}
