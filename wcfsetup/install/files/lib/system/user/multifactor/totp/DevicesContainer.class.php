<?php

namespace wcf\system\user\multifactor\totp;

use wcf\system\exception\InvalidObjectArgument;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\TDefaultIdFormField;
use wcf\system\form\builder\IFormChildNode;

/**
 * Shows the existing devices in a table.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\System\User\Multifactor\Totp
 * @since   5.4
 */
class DevicesContainer extends FormContainer
{
    use TDefaultIdFormField;

    /**
     * @var array
     */
    protected $devices = [];

    /**
     * @inheritDoc
     */
    protected $templateName = '__multifactorTotpDevicesContainer';

    /**
     * @inheritDoc
     */
    protected static function getDefaultId(): string
    {
        return 'devices';
    }

    /**
     * Accepts only DeviceNodes as children.
     */
    public function appendChild(IFormChildNode $child): self
    {
        if (!($child instanceof DeviceNode)) {
            throw new InvalidObjectArgument($child, DeviceNode::class, 'Child');
        }

        return parent::appendChild($child);
    }
}
