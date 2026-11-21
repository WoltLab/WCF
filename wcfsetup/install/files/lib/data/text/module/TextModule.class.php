<?php

namespace wcf\data\text\module;

use wcf\data\DatabaseObject;
use wcf\data\ITitledObject;
use wcf\data\language\Language;
use wcf\system\html\output\HtmlOutputProcessor;
use wcf\system\language\LanguageFactory;
use wcf\system\request\RequestHandler;
use wcf\system\WCF;

/**
 * Represents a text module, a reusable snippet that can be inserted into
 * messages.
 *
 * @author      Matthias Schmidt, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @property-read   int     $textModuleID   unique id of the text module
 * @property-read   ?int    $languageID     id of the language the text module is written in or `null` if it applies to all languages
 * @property-read   ?int    $userID         id of the user who created the text module or `null` if the user does not exist anymore
 * @property-read   string  $username       name of the user who created the text module
 * @property-read   string  $title          title of the text module
 * @property-read   string  $message        HTML of the text module
 */
final class TextModule extends DatabaseObject implements ITitledObject
{
    /**
     * @inheritDoc
     */
    protected static $databaseTableIndexName = 'textModuleID';

    #[\Override]
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Returns the language the text module is written in or `null` if it
     * applies to all languages.
     */
    public function getLanguage(): ?Language
    {
        if ($this->languageID === null) {
            return null;
        }

        return LanguageFactory::getInstance()->getLanguage($this->languageID);
    }

    /**
     * Returns the message as plain text.
     */
    public function getPlainTextMessage(): string
    {
        $processor = new HtmlOutputProcessor();
        $processor->setOutputType('text/plain');
        $processor->process($this->message, 'com.woltlab.wcf.textModule', $this->textModuleID);

        return $processor->getHtml();
    }

    /**
     * Returns true if the active user may use and manage text modules.
     */
    public static function canUse(): bool
    {
        if (\MODULE_TEXT_MODULE === 0) {
            return false;
        }

        if (RequestHandler::getInstance()->isACPRequest()) {
            return false;
        }

        return WCF::getSession()->hasPermission('mod.general.canUseTextModule');
    }
}
