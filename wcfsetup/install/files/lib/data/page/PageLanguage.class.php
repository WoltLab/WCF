<?php

namespace wcf\data\page;

use wcf\data\language\Language;
use wcf\system\language\LanguageFactory;
use wcf\system\request\LinkHandler;

/**
 * Represents a page language.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class PageLanguage
{
    /**
     * language id
     * @var int
     */
    protected $languageID;

    /**
     * page id
     * @var int
     */
    protected $pageID;

    public function __construct(int $pageID, int $languageID)
    {
        $this->pageID = $pageID;
        $this->languageID = $languageID;
    }

    /**
     * Returns the link to this version of the page.
     */
    public function getLink(): string
    {
        return LinkHandler::getInstance()->getCmsLink($this->pageID, $this->languageID);
    }

    /**
     * Returns the language of this version of the page.
     *
     * @return  Language
     */
    public function getLanguage()
    {
        return LanguageFactory::getInstance()->getLanguage($this->languageID);
    }
}
