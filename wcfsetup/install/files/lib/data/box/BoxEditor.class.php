<?php

namespace wcf\data\box;

use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\system\cache\eager\BoxCache;
use wcf\system\language\LanguageFactory;

/**
 * Provides functions to edit boxes.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @mixin       Box
 * @extends DatabaseObjectEditor<Box>
 * @implements IEditableCachedObject<Box>
 */
class BoxEditor extends DatabaseObjectEditor implements IEditableCachedObject
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = Box::class;

    /**
     * Creates the template file for "tpl"-type boxes.
     *
     * @return void
     */
    public function writeTemplate(int $languageID, string $content)
    {
        if ($this->getDecoratedObject()->boxType === 'tpl') {
            \file_put_contents(
                \WCF_DIR . 'templates/' . $this->getDecoratedObject()->getTplName(($languageID ?: null)) . '.tpl',
                $content
            );
        }
    }

    /**
     * Rebuilds the box cache, which also covers the box to page assignments and
     * the custom box show order of pages.
     */
    #[\Override]
    public static function resetCache()
    {
        foreach (LanguageFactory::getInstance()->getLanguages() as $language) {
            (new BoxCache($language->languageID))->rebuild();
        }
    }
}
