<?php

namespace wcf\system\cronjob;

use wcf\data\cronjob\Cronjob;
use wcf\system\language\LanguageFactory;
use wcf\system\package\PackageUpdateDispatcher;
use wcf\system\WCF;

/**
 * Fetches update package information.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Cronjob
 */
class GetUpdateInfoCronjob extends AbstractCronjob
{
    /**
     * @inheritDoc
     */
    public function execute(Cronjob $cronjob)
    {
        parent::execute($cronjob);

        if (!ENABLE_BENCHMARK) {
            try {
                $currentLanguage = WCF::getLanguage();
                // Always fetch package information using the default language.
                if ($currentLanguage->languageID !== LanguageFactory::getInstance()->getDefaultLanguage()->languageID) {
                    WCF::setLanguage(LanguageFactory::getInstance()->getDefaultLanguage());
                }

                PackageUpdateDispatcher::getInstance()->refreshPackageDatabase([], true);
            } finally {
                if ($currentLanguage->languageID !== LanguageFactory::getInstance()->getDefaultLanguage()->languageID) {
                    WCF::setLanguage($currentLanguage);
                }
            }
        }
    }
}
