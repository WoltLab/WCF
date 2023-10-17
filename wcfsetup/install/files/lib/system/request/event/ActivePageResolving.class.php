<?php

namespace wcf\system\request\event;

use wcf\data\page\Page;
use wcf\system\event\IEvent;
use wcf\system\request\Request;

/**
 * Indicates that the `RequestHandler` could not determine the active page.
 *
 * @author  Marcel Werk
 * @copyright   2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   6.0
 */
final class ActivePageResolving implements IEvent
{
    public ?Page $page = null;

    public function __construct(public readonly Request $request)
    {
    }
}
