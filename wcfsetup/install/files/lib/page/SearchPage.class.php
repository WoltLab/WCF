<?php

namespace wcf\page;

use wcf\system\search\ISearchProvider;
use wcf\system\search\SearchEngine;
use wcf\system\WCF;

/**
 * Shows the search form.
 *
 * @author  Marcel Werk
 * @copyright   2001-2021 WoltLab GmbH
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
        ]);
    }
}
