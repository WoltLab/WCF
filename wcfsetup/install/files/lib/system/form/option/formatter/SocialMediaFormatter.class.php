<?php

namespace wcf\system\form\option\formatter;

use wcf\util\StringUtil;

/**
 * Formatter for the names of social media profiles, which links the name to
 * the profile.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class SocialMediaFormatter implements IFormOptionFormatter
{
    public function __construct(
        private readonly bool $plainText = false,
    ) {}

    #[\Override]
    public function format(string $value, int $languageID, array $configuration): string
    {
        $url = self::getProfileUrl($value, $configuration);

        if ($this->plainText) {
            return $url ?? $value;
        }

        if ($url === null) {
            return StringUtil::encodeHTML($value);
        }

        return StringUtil::getAnchorTag($url, $value, true, true);
    }

    /**
     * Returns the link to the profile with the given name or `null` if no
     * valid link pattern is configured.
     *
     * @param array<string, mixed> $configuration
     */
    public static function getProfileUrl(string $profileName, array $configuration): ?string
    {
        $profileUrlPattern = (string)($configuration['profileUrlPattern'] ?? '');
        if (\substr_count($profileUrlPattern, '%s') !== 1) {
            return null;
        }

        $url = \sprintf($profileUrlPattern, \rawurlencode($profileName));
        if (!\preg_match('~^https?://~i', $url)) {
            return null;
        }

        return $url;
    }
}
