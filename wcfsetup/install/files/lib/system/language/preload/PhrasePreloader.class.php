<?php

namespace wcf\system\language\preload;

use wcf\data\language\Language;
use wcf\system\language\preload\command\CachePreloadPhrases;
use wcf\system\WCF;

/**
 * Provides the URL to the preload cache for
 * phrases and creates it if it is missing.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Language\Preload\Command
 * @since 6.0
 */
final class PhrasePreloader
{
    /**
     * Returns the URL to the preload cache. Will implicitly
     * create the cache if it does not exist.
     */
    public function getUrl(Language $language): string
    {
        if ($this->needsRebuild($language)) {
            $this->rebuild($language);
        }

        return WCF::getPath() . $language->getPreloadCacheFilename();
    }

    private function needsRebuild(Language $language): bool
    {
        return \file_exists(\WCF_DIR . $language->getPreloadCacheFilename());
    }

    private function rebuild(Language $language): void
    {
        $command = new CachePreloadPhrases($language);
        $command();
    }
}
