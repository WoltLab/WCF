<?php

namespace wcf\system\endpoint;

/**
 * Marks an API endpoint as available to guests and users awaiting activation
 * while the 'force login' option is enabled, for example to support the
 * registration process.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class AvailableDuringForceLogin {}
