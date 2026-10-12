<?php

namespace wcf\data\user\avatar;

/**
 * Provides an avatar in a broadly supported fallback image format, e.g. PNG
 * instead of WebP or SVG. Uploaded avatars fall back to their source file,
 * which can still be WebP if it was uploaded or converted as such.
 *
 * This interface exists solely for email templates: many email clients do not
 * support modern image formats or attributes such as `loading="lazy"`. Regular
 * frontend and backend output must use `IUserAvatar::getURL()` and
 * `IUserAvatar::getImageTag()` instead.
 *
 * @author  Tim Duesterhus, Marcel Werk
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 5.4
 */
interface ISafeFormatAvatar extends IUserAvatar
{
    /**
     * Returns the URL of this avatar in a format that is safe to use in
     * emails. Only intended for use in email templates.
     *
     * @see IUserAvatar::getURL()
     */
    public function getSafeURL(?int $size = null): string;

    /**
     * Returns the HTML code to display this avatar in emails. The image uses a
     * format that is safe to use in emails and is never lazy-loaded. Only
     * intended for use in email templates.
     *
     * @see IUserAvatar::getImageTag()
     */
    public function getSafeImageTag(?int $size = null): string;
}
