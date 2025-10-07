<?php

namespace wcf\system\cache\command;

use wcf\data\option\OptionEditor;
use wcf\data\package\update\server\PackageUpdateServer;
use wcf\system\cache\CacheHandler;
use wcf\system\cache\event\CacheCleared;
use wcf\system\event\EventHandler;
use wcf\system\language\LanguageFactory;
use wcf\system\style\StyleHandler;
use wcf\system\user\storage\UserStorageHandler;

/**
 * Performs a full cache clear.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   6.0
 */
final class ClearCache
{
    private EventHandler $eventHandler;

    public function __construct()
    {
        $this->eventHandler = EventHandler::getInstance();
    }

    public function __invoke(): void
    {
        OptionEditor::resetCache();

        UserStorageHandler::getInstance()->clear();

        StyleHandler::resetStylesheets();

        LanguageFactory::getInstance()->deleteLanguageCache();

        CacheHandler::getInstance()->flushAll();

        PackageUpdateServer::resetAll();

        $this->eventHandler->fire(
            new CacheCleared()
        );
    }
}
