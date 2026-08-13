<?php

namespace wcf\data\user;

use wcf\util\UserRegistrationUtil;

/**
 * Executes user registration-related actions.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserRegistrationAction extends UserAction
{
    /**
     * @inheritDoc
     */
    protected $allowGuestAccess = ['validateEmailAddress', 'validateUsername'];

    /**
     * Validates the validate username function.
     *
     * @return void
     */
    public function validateValidateUsername()
    {
        $this->readString('username');
    }

    /**
     * Validates the validate email address function.
     *
     * @return void
     */
    public function validateValidateEmailAddress()
    {
        $this->readString('email');
    }

    /**
     * Validates the given username.
     *
     * @return array{isValid: bool, error?: string}
     */
    public function validateUsername()
    {
        if (!UserRegistrationUtil::isValidUsername($this->parameters['username'])) {
            return [
                'isValid' => false,
                'error' => 'invalid',
            ];
        }

        if (!User::getUserByUsername($this->parameters['username'])->isGuest()) {
            return [
                'isValid' => false,
                'error' => 'notUnique',
            ];
        }

        return [
            'isValid' => true,
        ];
    }

    /**
     * Validates given email address.
     *
     * @return array{isValid: bool, error?: string}
     */
    public function validateEmailAddress()
    {
        if (!UserRegistrationUtil::isValidEmail($this->parameters['email'])) {
            return [
                'isValid' => false,
                'error' => 'invalid',
            ];
        }

        if (!User::getUserByEmail($this->parameters['email'])->isGuest()) {
            return [
                'isValid' => false,
                'error' => 'notUnique',
            ];
        }

        return [
            'isValid' => true,
        ];
    }
}
