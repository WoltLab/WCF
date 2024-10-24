<?php

namespace wcf\system\cache\builder;

use wcf\data\smiley\Smiley;
use wcf\system\WCF;

/**
 * Caches the smilies.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class SmileyCacheBuilder extends AbstractCacheBuilder
{
    /**
     * @inheritDoc
     */
    protected function rebuild(array $parameters)
    {
        $data = ['codes' => [], 'smilies' => []];

        // get smilies
        $sql = "SELECT      *
                FROM        wcf1_smiley
                ORDER BY    showOrder";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute();

        /** @var Smiley $object */
        while ($object = $statement->fetchObject(Smiley::class)) {
            $object->smileyCodes = $object->getAliases();
            $object->smileyCodes[] = $object->smileyCode;

            // this call will cause the image height to be added to the cache
            $object->getHeight();

            $data['smilies'][$object->categoryID][$object->smileyID] = $object;

            foreach ($object->smileyCodes as $smileyCode) {
                $data['codes'][$smileyCode] = $object;
            }
        }

        return $data;
    }
}
