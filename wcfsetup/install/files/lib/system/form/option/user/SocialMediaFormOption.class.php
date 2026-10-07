<?php

namespace wcf\system\form\option\user;

use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\SocialMediaFormField;
use wcf\system\form\option\formatter\IFormOptionFormatter;
use wcf\system\form\option\formatter\SocialMediaFormatter;
use wcf\system\form\option\TextFormOption;

/**
 * Implementation of a form option for the name of a social media profile,
 * which is linked to the profile using the configured link pattern.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class SocialMediaFormOption extends TextFormOption
{
    #[\Override]
    public function getId(): string
    {
        return 'socialMedia';
    }

    #[\Override]
    public function getFormField(string $id, array $configuration = []): AbstractFormField
    {
        return SocialMediaFormField::create($id)
            ->maximumLength(255)
            ->profileNamePattern(isset($configuration['profileNamePattern']) ? (string)$configuration['profileNamePattern'] : null)
            ->profileUrlPattern(isset($configuration['profileUrlPattern']) ? (string)$configuration['profileUrlPattern'] : null);
    }

    #[\Override]
    public function getConfigurationFormFields(): array
    {
        return ['profileNamePattern', 'profileUrlPattern', 'required'];
    }

    #[\Override]
    public function getFormatter(): IFormOptionFormatter
    {
        return new SocialMediaFormatter();
    }

    #[\Override]
    public function getPlainTextFormatter(): IFormOptionFormatter
    {
        return new SocialMediaFormatter(plainText: true);
    }

    #[\Override]
    public function renderFilterValue(string $value, array $configuration = []): string
    {
        // The filter matches parts of the profile name, a link would be misleading.
        return $value;
    }
}
