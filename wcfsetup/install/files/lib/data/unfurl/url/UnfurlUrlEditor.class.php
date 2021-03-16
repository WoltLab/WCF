<?php

namespace wcf\data\unfurl\url;

use wcf\data\DatabaseObjectEditor;

/**
 * Provide functions to edit an unfurl url.
 *
 * @author      Joshua Ruesweg
 * @copyright   2001-2021 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\Data\Unfurl\Url
 * @since       5.4
 *
 * @method  UnfurlUrl   getDecoratedObject()
 * @mixin   UnfurlUrl
 */
class UnfurlUrlEditor extends DatabaseObjectEditor
{
    /**
     * @inheritDoc
     */
    public static $baseClass = UnfurlUrl::class;
}
