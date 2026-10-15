<?php

namespace wcf\system\cache\builder;

use wcf\data\file\FileList;
use wcf\data\trophy\TrophyList;

/**
 * Caches the trophies.
 *
 * @author  Joshua Ruesweg, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class TrophyCacheBuilder extends AbstractCacheBuilder
{
    #[\Override]
    public function rebuild(array $parameters)
    {
        $trophyList = new TrophyList();

        if (!empty($parameters['onlyEnabled'])) {
            $trophyList->getConditionBuilder()->add('isDisabled = ?', [0]);
        }

        $trophyList->sqlOrderBy = 'trophy.showOrder ASC';
        $trophyList->readObjects();
        $trophies = $trophyList->getObjects();

        $fileIDs = [];
        foreach ($trophies as $trophy) {
            if ($trophy->iconFileID !== null) {
                $fileIDs[] = $trophy->iconFileID;
            }
        }

        if ($fileIDs !== []) {
            $fileList = new FileList();
            $fileList->setObjectIDs($fileIDs);
            $fileList->readObjects();

            foreach ($trophies as $trophy) {
                if ($trophy->iconFileID === null) {
                    continue;
                }

                $file = $fileList->search($trophy->iconFileID);
                if ($file !== null) {
                    $trophy->setIconFile($file);
                }
            }
        }

        // The trophies share one collection, loading the titles once stores
        // them in the cache instead of querying them on every request.
        if ($trophies !== []) {
            \reset($trophies)->getL10nValues('title');
        }

        return $trophies;
    }
}
