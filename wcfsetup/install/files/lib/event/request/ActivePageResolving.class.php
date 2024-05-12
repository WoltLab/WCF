<?php

namespace wcf\event\request;

use wcf\data\page\Page;
use wcf\event\IPsr14Event;
use wcf\system\request\Request;

/**
 * Indicates that the `RequestHandler` could not determine the active page.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class ActivePageResolving implements IPsr14Event
{
    public ?Page $page = null;

    public function __construct(public readonly Request $request)
    {
    }
}
