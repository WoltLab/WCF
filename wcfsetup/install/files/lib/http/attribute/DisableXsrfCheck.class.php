<?php

namespace wcf\http\attribute;

/**
 * Disables the built-in XSRF validation of PSR-15 controllers.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class DisableXsrfCheck
{
}
