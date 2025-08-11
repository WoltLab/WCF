<?php

namespace wcf\event\page;

use wcf\data\page\Page;
use wcf\event\IPsr14Event;

/**
 * Indicates that a page has been enabled.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class PageEnabled implements IPsr14Event
{
    public function __construct(public readonly Page $page) {}
}
