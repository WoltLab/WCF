<?php

namespace wcf\system\form\builder\field\devtools\project;

use wcf\data\package\Package;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\TDefaultIdFormField;

/**
 * Form field implementation for the optional packages of a devtools project.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
final class DevtoolsProjectOptionalPackagesFormField extends AbstractFormField
{
    use TDefaultIdFormField;

    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_devtoolsProjectOptionalPackagesFormField';

    /**
     * @inheritDoc
     */
    protected $value = [];

    /**
     * @inheritDoc
     */
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

    /**
     * @inheritDoc
     */
    public function validate()
    {
        // everything is already validated by JavaScript thus we skip
        // reporting specific errors and simply remove manipulated values
        $optionalPackages = [];
        $packageIdentifiers = [];
        foreach ($this->getValue() as $package) {
            // ensure that all relevant elements are present
            if (!\is_array($package) || !isset($package['packageIdentifier'])) {
                continue;
            }

            // validate package identifier
            if (
                !Package::isValidPackageName($package['packageIdentifier'])
                || \in_array($package['packageIdentifier'], $packageIdentifiers)
            ) {
                continue;
            }

            $optionalPackages[] = $package;
        }

        $this->value($optionalPackages);
    }

    /**
     * @inheritDoc
     */
    protected static function getDefaultId()
    {
        return 'optionalPackages';
    }
}
