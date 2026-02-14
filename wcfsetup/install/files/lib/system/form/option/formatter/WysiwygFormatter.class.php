<?php

namespace wcf\system\form\option\formatter;

use wcf\system\html\output\HtmlOutputProcessor;

/**
 * Formatter for wysiwyg form options.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class WysiwygFormatter implements IFormOptionFormatter
{
    public function __construct(
        private readonly string $objectType,
        private readonly int $objectID,
    ) {}

    #[\Override]
    public function format(string $value, int $languageID, array $configuration): string
    {
        $processor = new HtmlOutputProcessor();
        $processor->process($value, $this->objectType, $this->objectID, true, $languageID);

        return $processor->getHtml();
    }
}
