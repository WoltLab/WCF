<?php

namespace wcf\system\package\plugin;

/**
 * Every PackageInstallationPlugin, which have unique names must be implement this interface
 * to enforce, that only unique names will be processed.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Package\Plugin
 * @since 5.5
 */
interface IUniqueNameXMLPackageInstallationPlugin
{
    /**
     * Returns the name of an element by the given data.
     */
    public function getNameByData(array $data): string;
}
