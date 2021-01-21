<?php

namespace wcf\system\user\multifactor\totp;

use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\TDefaultIdFormField;

/**
 * Shows the form to add a new TOTP device.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\System\User\Multifactor\Totp
 * @since   5.4
 */
class NewDeviceContainer extends FormContainer
{
    use TDefaultIdFormField;

    /**
     * @inheritDoc
     */
    protected $templateName = '__multifactorTotpNewDeviceContainer';

    /**
     * @inheritDoc
     */
    protected static function getDefaultId(): string
    {
        return 'newDevice';
    }
}
