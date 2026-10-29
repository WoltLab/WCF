<?php

namespace wcf\system\cache\eager;

use wcf\data\style\Style;
use wcf\system\WCF;

/**
 * Eager cache of the styles, their variables and the image dimensions derived
 * from them. It consists of plain arrays only, which allows the OPcache to keep
 * it in shared memory, the `Style` objects are created by `StyleHandler`.
 *
 * @author Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 *
 * @extends AbstractEagerCache<array{
 *     default: int,
 *     styles: array<int, array{
 *         data: array<string, mixed>,
 *         variables: array<string, ?string>,
 *         coverPhotoHeight: int,
 *         coverPhotoWidth: int,
 *         pageLogoSmallHeight: int,
 *         pageLogoSmallWidth: int,
 *     }>,
 * }>
 */
final class StyleCache extends AbstractEagerCache
{
    #[\Override]
    protected function getCacheData(): array
    {
        $data = [
            'default' => 0,
            'styles' => [],
        ];

        $sql = "SELECT      *
                FROM        wcf1_style
                ORDER BY    styleName ASC";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute();
        while ($row = $statement->fetchArray()) {
            if ($row['isDefault'] !== 0) {
                $data['default'] = $row['styleID'];
            }

            $data['styles'][$row['styleID']] = [
                'data' => $row,
                ...new Style(null, $row)->exportCacheData(),
            ];
        }

        return $data;
    }
}
