<?php

namespace wcf\system\form\option\formatter;

use GuzzleHttp\Psr7\Exception\MalformedUriException;
use GuzzleHttp\Psr7\Uri;
use wcf\util\StringUtil;

/**
 * Formatter for URL values.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class UrlFormatter implements IFormOptionFormatter
{
    #[\Override]
    public function format(string $value, int $languageID, array $configuration): string
    {
        return StringUtil::getAnchorTag($value, $this->getTruncatedTitle($value), true, true);
    }

    private function getTruncatedTitle(string $href): string
    {
        if (\mb_strlen($href) <= 60) {
            return $href;
        }

        try {
            $uri = new Uri($href);
        } catch (MalformedUriException) {
            return $href;
        }

        $schemeHost = Uri::composeComponents(
            $uri->getScheme(),
            $uri->getAuthority(),
            '',
            null,
            null,
        );
        $pathQueryFragment = Uri::composeComponents(
            null,
            null,
            $uri->getPath(),
            $uri->getQuery(),
            $uri->getFragment(),
        );
        if (\mb_strlen($pathQueryFragment) > 35) {
            $pathQueryFragment = \mb_substr($pathQueryFragment, 0, 15) . StringUtil::HELLIP . \mb_substr($pathQueryFragment, -15);
        }

        return $schemeHost . $pathQueryFragment;
    }
}
