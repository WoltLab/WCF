<?php

namespace wcf\page;

use wcf\form\TagSearchForm;
use wcf\system\request\LinkHandler;
use wcf\system\search\ISearchProvider;
use wcf\system\search\SearchEngine;
use wcf\system\view\component\ContentInteractionTabsComponent;
use wcf\system\WCF;

/**
 * Shows the search form.
 *
 * @author  Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 5.5
 */
final class SearchPage extends AbstractPage
{
    public bool $extended = false;

    #[\Override]
    public function readParameters(): void
    {
        parent::readParameters();

        if (!empty($_REQUEST['extended'])) {
            $this->extended = true;
        }
    }

    #[\Override]
    public function assignVariables(): void
    {
        parent::assignVariables();

        foreach (SearchEngine::getInstance()->getAvailableObjectTypes() as $objectType) {
            if ($objectType instanceof ISearchProvider) {
                $objectType->assignVariables();
            } else {
                $objectType->show();
            }
        }

        WCF::getTPL()->assign([
            'objectTypes' => SearchEngine::getInstance()->getAvailableObjectTypes(),
            'sortField' => \SEARCH_DEFAULT_SORT_FIELD,
            'sortOrder' => \SEARCH_DEFAULT_SORT_ORDER,
            'extended' => $this->extended,
            'contentInteractionTabsComponent' => $this->getContentInteractionTabsComponent(),
        ]);
    }

    private function getContentInteractionTabsComponent(): ?ContentInteractionTabsComponent
    {
        if (\MODULE_TAGGING === 0 || !WCF::getSession()->hasPermission('user.tag.canViewTag')) {
            return null;
        }

        $component = new ContentInteractionTabsComponent();
        $component->addTab(
            'wcf.search.type.text',
            LinkHandler::getInstance()->getControllerLink(self::class),
            true
        );
        $component->addTab(
            'wcf.search.type.tags',
            LinkHandler::getInstance()->getControllerLink(TagSearchForm::class)
        );

        return $component;
    }
}
