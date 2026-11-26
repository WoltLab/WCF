<?php

namespace wcf\system\form\builder\field\devtools\project;

use wcf\data\package\Package;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\TDefaultIdFormField;

/**
 * Form field implementation for the suggested packages of a devtools project.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   6.3
 */
final class DevtoolsProjectSuggestedPackagesFormField extends AbstractFormField
{
    use TDefaultIdFormField;

    /**
     * @inheritDoc
     */
    protected $templateName = '__devtoolsProjectSuggestedPackagesFormField';

    /**
     * @inheritDoc
     */
    protected $value = [];

    #[\Override]
    public function readValue()
    {
        if (
            $this->getDocument()->hasRequestData($this->getPrefixedId())
            && \is_array($this->getDocument()->getRequestData($this->getPrefixedId()))
        ) {
            $this->value = $this->getDocument()->getRequestData($this->getPrefixedId());
        } else {
            $this->value = [];
        }

        return $this;
    }

    #[\Override]
    public function validate()
    {
        // The JavaScript already validates the input, manipulated values are silently dropped.
        // Package identifiers are case-insensitive, `PackageArchive` drops duplicates that only differ in case.
        $suggestedPackages = [];
        foreach ($this->getValue() as $package) {
            if (
                !\is_array($package)
                || !isset($package['packageIdentifier'])
                || !\is_string($package['packageIdentifier'])
            ) {
                continue;
            }

            $normalizedIdentifier = \strtolower($package['packageIdentifier']);
            if (
                !Package::isValidPackageName($package['packageIdentifier'])
                || isset($suggestedPackages[$normalizedIdentifier])
            ) {
                continue;
            }

            $suggestedPackages[$normalizedIdentifier] = [
                'packageIdentifier' => $package['packageIdentifier'],
            ];
        }

        $this->value(\array_values($suggestedPackages));
    }

    #[\Override]
    protected static function getDefaultId(): string
    {
        return 'suggestedPackages';
    }
}
