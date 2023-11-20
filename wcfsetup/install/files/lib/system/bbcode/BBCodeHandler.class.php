<?php

namespace wcf\system\bbcode;

use wcf\data\bbcode\BBCode;
use wcf\data\bbcode\BBCodeCache;
use wcf\system\application\ApplicationHandler;
use wcf\system\package\license\LicenseApi;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\JSON;
use wcf\util\StringUtil;

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
     */
    public function isAvailableBBCode(string $bbCodeTag, bool $overrideFormattingRemoval = false): bool
    {
        if ($overrideFormattingRemoval === false) {
            if ($bbCodeTag === "color" && \FORMATTING_REMOVE_COLOR) {
                return false;
            }

            if ($bbCodeTag === "font" && \FORMATTING_REMOVE_FONT) {
                return false;
            }

            if ($bbCodeTag === "size" && \FORMATTING_REMOVE_SIZE) {
                return false;
            }
        }

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
     * Returns the list of languages that are available for selection in the
     * UI of CKEditor’s code block.
     *
     * @return list<string>
     * @since 6.0
     */
    public function getCodeBlockLanguages(): array
    {
        return \explode("\n", StringUtil::unifyNewlines(\MESSAGE_PUBLIC_HIGHLIGHTERS));
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

        $locale = \strtolower(WCF::getLanguage()->getBcp47());
        if (\in_array($locale, $availableTranslations, true)) {
            return \sprintf(
                '"ckeditor5-translation/%s",',
                $locale
            );
        }

        // Some languages offer both specialized variants for certain locales
        // but also provide a "generic" variant. For example, "en-gb" and "en".
        [$languageCode] = \explode('-', $locale, 2);
        if (\in_array($languageCode, $availableTranslations, true)) {
            return \sprintf(
                '"ckeditor5-translation/%s",',
                $languageCode
            );
        }

        // The default locale "en" is part of the generated bundle, we must not
        // yield any module if this locale is (implicitly) requested.
        return "";
    }

    /**
     * @since 6.0
     */
    public function getCkeditorLicenseKey(): string
    {
        $licenseApi = new LicenseApi();
        $licenseData = $licenseApi->readFromFile();

        if ($licenseData === null) {
            return '';
        }

        return $licenseData->license['ckeditorLicenseKey'] ?? '';
    }
}
