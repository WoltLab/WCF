<?php

namespace wcf\event\contact\option;

use wcf\data\contact\option\ContactOption;
use wcf\event\IPsr14Event;

/**
 * Indicates that a contact option has been enabled.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class ContactOptionEnabled implements IPsr14Event
{
    public function __construct(public readonly ContactOption $contactOption) {}
}
