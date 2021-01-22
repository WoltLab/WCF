<?php

namespace wcf\system\user\multifactor\totp;

use wcf\system\form\builder\IFormChildNode;
use wcf\system\form\builder\IFormParentNode;
use wcf\system\form\builder\TFormChildNode;
use wcf\system\form\builder\TFormElement;
use wcf\system\form\builder\TFormParentNode;
use wcf\system\WCF;

/**
 * Shows a devices as a table row.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\System\User\Multifactor\Totp
 * @since   5.4
 */
class DeviceNode implements IFormChildNode, IFormParentNode
{
    use TFormElement;
    use TFormChildNode;
    use TFormParentNode {
        TFormParentNode::cleanup insteadof TFormElement;
    }

    /**
     * @var ?array
     */
    protected $data;

    /**
     * @inheritDoc
     */
    protected $templateName = '__multifactorTotpDeviceNode';

    /**
     * @inheritDoc
     */
    public function getHtml(): string
    {
        return WCF::getTPL()->fetch(
            $this->templateName,
            'wcf',
            [
                'container' => $this,
                'device' => $this->data,
            ],
            true
        );
    }

    /**
     * Sets the device data.
     */
    public function setData(array $device): self
    {
        $this->data = $device;

        return $this;
    }

    /**
     * Returns true once device data has been set.
     */
    public function isAvailable(): bool
    {
        return $this->data !== null;
    }

    /**
     * @inheritDoc
     */
    public function checkDependencies(): bool
    {
        if (!empty($this->dependencies)) {
            foreach ($this->dependencies as $dependency) {
                // check dependencies directly and check if a dependent
                // field itself is unavailable because of its dependencies
                if (!$dependency->checkDependency() || !$dependency->getField()->checkDependencies()) {
                    return false;
                }
            }
        }

        return true;
    }
}
