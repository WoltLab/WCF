<?php

namespace wcf\data\page\content;

use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit page content.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @mixin       PageContent
 * @extends DatabaseObjectEditor<PageContent>
 */
class PageContentEditor extends DatabaseObjectEditor
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = PageContent::class;
}
