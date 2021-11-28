<?php

namespace wcf\page;

use wcf\system\search\SearchEngine;
use wcf\system\WCF;

/**
 * Shows the search form.
 *
 * @author  Marcel Werk
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Page
 */
class SearchPage extends AbstractPage
{
    /**
     * @var bool
     */
    public $extended = false;

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (!empty($_REQUEST['extended'])) $this->extended = true;
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        foreach (SearchEngine::getInstance()->getAvailableObjectTypes() as $objectType) {
            $objectType->show();
        }

        WCF::getTPL()->assign([
            'objectTypes' => SearchEngine::getInstance()->getAvailableObjectTypes(),
            'sortField' => SEARCH_DEFAULT_SORT_FIELD,
            'sortOrder' => SEARCH_DEFAULT_SORT_ORDER,
            'extended' => $this->extended,
        ]);
    }
}
