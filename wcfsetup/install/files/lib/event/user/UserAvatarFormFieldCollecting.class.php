<?php

namespace wcf\event\user;

use wcf\data\user\UserProfile;
use wcf\event\IPsr14Event;
use wcf\system\form\builder\field\IFormField;

/**
 * Requests the collection of form fields used in the user avatar dialog.
 *
 * @author Olaf Braun
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
final class UserAvatarFormFieldCollecting implements IPsr14Event
{
    /**
     * @var array<string, IFormField>
     */
    private array $formFields = [];

    public function __construct(public readonly UserProfile $user)
    {
    }

    /**
     * Registers a new form field to be used in the user avatar dialog.
     */
    public function register(IFormField $formField): void
    {
        $this->formFields[$formField->getId()] = $formField;
    }

    /**
     * @return array<string, IFormField>
     */
    public function getFormFields(): array
    {
        return $this->formFields;
    }
}
