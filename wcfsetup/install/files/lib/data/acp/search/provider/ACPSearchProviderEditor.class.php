<?php

namespace wcf\data\acp\search\provider;

use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit acp search providers.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @mixin       ACPSearchProvider
 * @extends DatabaseObjectEditor<ACPSearchProvider>
 *
 * @deprecated 6.3
 */
class ACPSearchProviderEditor extends DatabaseObjectEditor
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = ACPSearchProvider::class;
}
