<?php

namespace wcf\system\interaction;

/**
 * Represents an effect that is to be applied after an interaction has been executed.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
enum InteractionEffect
{
    case ReloadItem;
    case ReloadList;
    case ReloadPage;
    case RemoveItem;

    /**
     * Navigates to the URL provided by the `redirectUrl` property of the response.
     *
     * @since 6.3
     */
    case Redirect;

    public function toString(): string
    {
        return match ($this) {
            self::ReloadItem => 'ReloadItem',
            self::ReloadList => 'ReloadList',
            self::ReloadPage => 'ReloadPage',
            self::RemoveItem => 'RemoveItem',
            self::Redirect => 'Redirect',
        };
    }
}
