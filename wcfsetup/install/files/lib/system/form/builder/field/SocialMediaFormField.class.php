<?php

namespace wcf\system\form\builder\field;

use wcf\data\language\Language;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\Regex;

/**
 * Implementation of a form field to enter the name of a social media profile.
 *
 * If the link to the profile is entered instead, only the name is kept. The
 * name can be validated using a regular expression.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class SocialMediaFormField extends TextFormField
{
    /**
     * regular expression without delimiters that the profile name must match
     */
    private ?string $profileNamePattern = null;

    /**
     * link to the profile with `%s` as the placeholder for the profile name
     */
    private ?string $profileUrlPattern = null;

    /**
     * Sets the regular expression without delimiters that the profile name must match.
     */
    public function profileNamePattern(?string $profileNamePattern): static
    {
        $this->profileNamePattern = $profileNamePattern !== '' ? $profileNamePattern : null;

        return $this;
    }

    public function getProfileNamePattern(): ?string
    {
        return $this->profileNamePattern;
    }

    /**
     * Sets the link to the profile with `%s` as the placeholder for the profile name.
     */
    public function profileUrlPattern(?string $profileUrlPattern): static
    {
        $this->profileUrlPattern = $profileUrlPattern !== '' ? $profileUrlPattern : null;

        return $this;
    }

    public function getProfileUrlPattern(): ?string
    {
        return $this->profileUrlPattern;
    }

    #[\Override]
    public function readValue()
    {
        parent::readValue();

        if (\is_string($this->value)) {
            $this->value = $this->extractProfileName($this->value);
        }

        return $this;
    }

    /**
     * Returns the name of the profile if the given value is the link to the
     * profile, otherwise the given value is returned.
     */
    private function extractProfileName(string $value): string
    {
        if ($this->profileUrlPattern === null || \substr_count($this->profileUrlPattern, '%s') !== 1) {
            return $value;
        }

        [$prefix, $suffix] = \explode('%s', $this->profileUrlPattern);

        // The scheme and the `www.` subdomain are optional.
        $prefix = \preg_replace('~^https?://(www\.)?~i', '', $prefix);
        $pattern = '~^(?:https?://)?(?:www\.)?' . \preg_quote($prefix, '~')
            . '(?<name>[^/?#]+)' . \preg_quote(\rtrim($suffix, '/'), '~') . '/?$~i';

        if (\preg_match($pattern, $value, $matches)) {
            return \rawurldecode($matches['name']);
        }

        return $value;
    }

    #[\Override]
    protected function validateText(string $text, ?Language $language = null)
    {
        parent::validateText($text, $language);

        if (
            $text !== ''
            && $this->profileNamePattern !== null
            && Regex::compile($this->profileNamePattern)->match($text) === 0
        ) {
            $this->addValidationError(new FormFieldValidationError(
                'profileName',
                'wcf.form.option.socialMedia.error.profileName'
            ));
        }
    }
}
