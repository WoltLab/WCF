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
 * @package WoltLabSuite\Core\System\Form\Builder\Field\Devtools\Project
 * @since   5.2
 */
class DevtoolsProjectOptionalPackagesFormField extends AbstractFormField
{
    use TDefaultIdFormField;

    /**
     * @inheritDoc
     */
    protected $templateName = '__devtoolsProjectOptionalPackagesFormField';

    /**
     * @inheritDoc
     */
    protected $value = [];

    /**
     * @inheritDoc
     */
    public function readValue()
    {
        if ($this->getDocument()->hasRequestData($this->getPrefixedId()) && \is_array($this->getDocument()->getRequestData($this->getPrefixedId()))) {
            $this->value = $this->getDocument()->getRequestData($this->getPrefixedId());
        } else {
            $this->value = [];
        }
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        // everything is already validated by JavaScript thus we skip
        // reporting specific errors and simply remove manipulated values
        $excludedPackages = [];
        $packageIdentifiers = [];
        foreach ($this->getValue() as $package) {
            // ensure that all relevant elements are present
            if (!\is_array($package) || !isset($package['packageIdentifier'])) {
                continue;
            }

            // validate package identifier
            if (!Package::isValidPackageName($package['packageIdentifier']) || \in_array($package['packageIdentifier'], $packageIdentifiers)) {
                continue;
            }

            $excludedPackages[] = $package;
        }

        $this->value($excludedPackages);
    }

    /**
     * @inheritDoc
     */
    protected static function getDefaultId()
    {
        return 'optionalPackages';
    }
}
