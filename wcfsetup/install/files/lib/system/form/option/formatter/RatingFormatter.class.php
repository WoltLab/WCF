<?php

namespace wcf\system\form\option\formatter;

use wcf\system\style\FontAwesomeIcon;

/**
 * Formatter for rating values.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class RatingFormatter implements IFormOptionFormatter
{
    #[\Override]
    public function format(string $value, int $languageID, array $configuration): string
    {
        $rating = \floatval($value);
        $html = '';

        for ($i = 1; $i <= 5; $i++) {
            if ($rating >= $i) {
                $html .= FontAwesomeIcon::fromString('star;true')->toHtml();
            } else if ($rating + 0.5 >= $i) {
                $html .= FontAwesomeIcon::fromString('star-half-stroke;true')->toHtml();
            } else {
                $html .= FontAwesomeIcon::fromString('star;false')->toHtml();
            }
        }

        return $html;
    }
}
