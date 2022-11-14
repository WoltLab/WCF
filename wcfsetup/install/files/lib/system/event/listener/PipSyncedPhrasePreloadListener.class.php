<?php

namespace wcf\system\event\listener;

use wcf\system\language\LanguageFactory;
use wcf\system\language\preload\command\ResetPreloadCache;
use wcf\system\package\event\PackageInstallationPluginSynced;

/**
 * Resets the preload cache when certain PIPs have been synced.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Event\Listener
 * @since 6.0
 */
final class PipSyncedPhrasePreloadListener
{
    private readonly LanguageFactory $languageFactory;

    public function __construct()
    {
        $this->languageFactory = LanguageFactory::getInstance();
    }

    public function __invoke(PackageInstallationPluginSynced $event): void
    {
        if ($event->isInvokedAgain) {
            return;
        }

        if ($event->pluginName === 'file' || $event->pluginName === 'language') {
            foreach ($this->languageFactory->getLanguages() as $language) {
                $command = new ResetPreloadCache($language);
                $command();
            }
        }
    }
}
