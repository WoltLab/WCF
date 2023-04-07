<?php

namespace wcf\system\bbcode;

use wcf\data\bbcode\BBCode;
use wcf\data\bbcode\BBCodeCache;
use wcf\system\application\ApplicationHandler;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\JSON;

/**
 * Handles BBCodes displayed as buttons within the WYSIWYG editor.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class BBCodeHandler extends SingletonFactory
{
    /**
     * list of BBCodes displayed as buttons
     * @var BBCode[]
     */
    protected $buttonBBCodes = [];

    /**
     * list of BBCodes disallowed for usage
     * @var BBCode[]
     */
    protected $disallowedBBCodes = [];

    /**
     * list of BBCodes which contain raw code (disabled BBCode parsing)
     * @var BBCode[]
     */
    protected $sourceBBCodes;

    /**
     * meta information about highlighters
     * @var mixed[]
     */
    protected $highlighterMeta;

    /**
     * @inheritDoc
     */
    protected function init()
    {
        foreach (BBCodeCache::getInstance()->getBBCodes() as $bbcode) {
            if ($bbcode->showButton) {
                $this->buttonBBCodes[] = $bbcode;
            }
        }
    }

    /**
     * Returns true if the BBCode with the given tag is available in the WYSIWYG editor.
     *
     * @param string $bbCodeTag
     * @return  bool
     */
    public function isAvailableBBCode($bbCodeTag)
    {
        return !\in_array($bbCodeTag, $this->disallowedBBCodes);
    }

    /**
     * Returns all bbcodes.
     *
     * @return  BBCode[]
     */
    public function getBBCodes()
    {
        return BBCodeCache::getInstance()->getBBCodes();
    }

    /**
     * Returns a list of BBCodes displayed as buttons.
     *
     * @param bool $excludeCoreBBCodes do not return bbcodes that are available by default
     * @return  BBCode[]
     */
    public function getButtonBBCodes($excludeCoreBBCodes = false)
    {
        $buttons = [];
        $coreBBCodes = [
            'align',
            'b',
            'code',
            'color',
            'html',
            'i',
            'img',
            'list',
            's',
            'size',
            'sub',
            'sup',
            'quote',
            'spoiler',
            'table',
            'tt',
            'u',
            'url',
        ];
        foreach ($this->buttonBBCodes as $bbcode) {
            if ($excludeCoreBBCodes && \in_array($bbcode->bbcodeTag, $coreBBCodes)) {
                continue;
            }

            if ($this->isAvailableBBCode($bbcode->bbcodeTag)) {
                $buttons[] = $bbcode;
            }
        }

        return $buttons;
    }

    /**
     * Sets the disallowed BBCodes.
     *
     * @param string[] $bbCodes
     */
    public function setDisallowedBBCodes(array $bbCodes)
    {
        $this->disallowedBBCodes = $bbCodes;
    }

    /**
     * Returns a list of BBCodes which contain raw code (disabled BBCode parsing)
     *
     * @return  BBCode[]
     * @deprecated  3.1 - This method is no longer supported.
     */
    public function getSourceBBCodes()
    {
        return [];
    }

    /**
     * Returns metadata about the highlighters.
     *
     * @return  string[][]
     */
    public function getHighlighterMeta()
    {
        if ($this->highlighterMeta === null) {
            $this->highlighterMeta = JSON::decode(\preg_replace(
                '/.*\/\*!START\*\/\s*const\s*metadata\s*=\s*(.*)\s*;\s*\/\*!END\*\/.*/s',
                '\\1',
                \file_get_contents(WCF_DIR . '/js/WoltLabSuite/Core/prism-meta.js')
            ));
        }

        return $this->highlighterMeta;
    }

    /**
     * Returns a list of known highlighters.
     *
     * @return  string[]
     */
    public function getHighlighters()
    {
        return \array_keys($this->getHighlighterMeta());
    }

    /**
     * Returns a list of hostnames that are permitted as image sources.
     *
     * @return string[]
     * @since 5.2
     */
    public function getImageExternalSourceWhitelist()
    {
        $hosts = [];
        // Hide these hosts unless external sources are actually denied.
        if (!IMAGE_ALLOW_EXTERNAL_SOURCE) {
            $hosts = ArrayUtil::trim(\explode(
                "\n",
                \sprintf(
                    "%s\n%s",
                    \IMAGE_EXTERNAL_SOURCE_WHITELIST,
                    \INTERNAL_HOSTNAMES
                )
            ));
        }

        $hosts[] = ApplicationHandler::getInstance()->getDomainName();

        return \array_unique($hosts);
    }

    /**
     * Exports a require.js requirement for the localization of the editor based
     * on the current locale. Returns an empty string when there is no available
     * localization or the locale equals the bundled value 'en'.
     *
     * @since 6.0
     */
    public function getEditorLocalization(): string
    {
        $availableTranslations = [
            'af',
            'ar',
            'ast',
            'az',
            'bg',
            'bn',
            'bs',
            'ca',
            'cs',
            'da',
            'de-ch',
            'de',
            'el',
            'en-au',
            'en-gb',
            'eo',
            'es-co',
            'es',
            'et',
            'eu',
            'fa',
            'fi',
            'fr',
            'gl',
            'gu',
            'he',
            'hi',
            'hr',
            'hu',
            'id',
            'it',
            'ja',
            'jv',
            'kk',
            'km',
            'kn',
            'ko',
            'ku',
            'lt',
            'lv',
            'ms',
            'nb',
            'ne',
            'nl',
            'no',
            'oc',
            'pl',
            'pt-br',
            'pt',
            'ro',
            'ru',
            'si',
            'sk',
            'sl',
            'sq',
            'sr-latn',
            'sr',
            'sv',
            'th',
            'tk',
            'tr',
            'tt',
            'ug',
            'uk',
            'ur',
            'uz',
            'vi',
            'zh-cn',
            'zh',
        ];

        $locale = WCF::getLanguage()->getBcp47();
        if (\in_array($locale, $availableTranslations, true)) {
            return \sprintf(
                '"ckeditor5-translation/%s",',
                $locale
            );
        }

        $index = \strpos($locale, '-');
        if ($index !== false) {
            $locale = \substr($locale, 0, $index);

            if (\in_array($locale, $availableTranslations, true)) {
                return \sprintf(
                    '"ckeditor5-translation/%s",',
                    $locale
                );
            }
        }


        return "";
    }
}
