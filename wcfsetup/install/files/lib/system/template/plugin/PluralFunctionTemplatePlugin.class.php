<?php

namespace wcf\system\template\plugin;

use wcf\system\exception\SystemException;
use wcf\system\template\TemplateEngine;
use wcf\system\WCF;

/**
 * Template function plugin which generate plural phrases.
 *
 * Languages vary in how they handle plurals of nouns or unit expressions.
 * Some languages have two forms, like English; some languages have only a
 * single form; and some languages have multiple forms.
 *
 * Supported parameters:
 * value (number|array|Countable - required)
 * other (string - required)
 * zero (string), one (string), two (string), few (string), many (string)
 *
 * Usage:
 *      {plural value=$number zero='0' one='1' two='2' few='few' many='many' other='#'}
 *      There {plural value=$worlds one='is one world' other='are # worlds'}
 *      Updated {plural value=$minutes 0='just now' 1='one minute ago' other='# minutes ago'}
 *
 * @author  Marcel Werk
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.3
 */
final class PluralFunctionTemplatePlugin implements IFunctionTemplatePlugin
{
    /**
     * CLDR categories supported by the ICU message parser.
     */
    private const CATEGORIES = ['zero', 'one', 'two', 'few', 'many', 'other'];

    /**
     * @inheritDoc
     */
    public function execute($tagArgs, TemplateEngine $tplObj)
    {
        if (!isset($tagArgs['value'])) {
            if (!\array_key_exists('value', $tagArgs)) {
                throw new SystemException("Missing attribute 'value'");
            } else {
                throw new SystemException("Attribute 'value' must not be null");
            }
        }
        if (!isset($tagArgs['other'])) {
            throw new SystemException("Missing attribute 'other'");
        }

        $value = $tagArgs['value'];
        if (\is_countable($value)) {
            $value = \count($value);
        } else if (\is_numeric($value) && \floor($value) != $value) {
            // ICU represents fractional values with up to 3 decimal places
            // which differs from the behavior in `StringUtil::formatNumeric`.
            // The weak comparison of the rounded value allows us to detect
            // decimal places while leaving integer values as-is.
            $value = \round($value, 2);
        }

        // handle numeric attributes
        foreach ($tagArgs as $key => $_value) {
            if (\is_numeric($key)) {
                if ($key == $value) {
                    return $_value;
                }
            }
        }

        return \MessageFormatter::formatMessage(
            WCF::getLanguage()->getLocale(),
            $this->createMessageFromValues($tagArgs),
            ['value' => $value],
        );
    }

    /**
     * Creates a message string that is understood by the ICU message parser.
     *
     * This extra step is required because the existing implementation for
     * plurals resolved the category and then picked the appropriate value
     * itself.
     */
    private function createMessageFromValues(array $values): string
    {
        $items = [];
        foreach ($values as $category => $value) {
            if (!\in_array($category, self::CATEGORIES)) {
                continue;
            }

            $items[] = \sprintf(
                '%s{%s}',
                $category,
                // ICU requires apostrophes (U+0027) to be escaped.
                \str_replace("'", "''", $value),
            );
        }

        return \sprintf(
            '{value, plural, %s}',
            \implode(' ', $items),
        );
    }
}
