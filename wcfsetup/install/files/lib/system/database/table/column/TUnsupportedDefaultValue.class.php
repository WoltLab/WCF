<?php

namespace wcf\system\database\table\column;

/**
 * Disallows the use of default values for BLOB or TEXT types.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Database\Table\Column
 * @since 5.4
 */
trait TUnsupportedDefaultValue
{
    public function defaultValue($defaultValue)
    {
        if ($defaultValue !== null) {
            throw new \BadMethodCallException("Default values for BLOB or TEXT columns are unsupported.");
        }

        return $this;
    }
}
