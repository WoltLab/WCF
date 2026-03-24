<?php

namespace wcf\data\page\content;

use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes page content related actions.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectAction<PageContent, PageContentEditor>
 */
class PageContentAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    protected $className = PageContentEditor::class;
}
