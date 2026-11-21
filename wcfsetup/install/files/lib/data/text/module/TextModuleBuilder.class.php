<?php

namespace wcf\data\text\module;

use wcf\data\DatabaseObjectBuilder;
use wcf\data\user\User;

/**
 * Builder for creating, updating and deleting text modules.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectBuilder<TextModule>
 */
final class TextModuleBuilder extends DatabaseObjectBuilder
{
    public function setTitle(string $title): static
    {
        $this->properties['title'] = $title;

        return $this;
    }

    /**
     * Sets the processed HTML of the text module.
     */
    public function setMessage(string $message): static
    {
        $this->properties['message'] = $message;

        return $this;
    }

    /**
     * Sets the language the text module is written in, `null` makes the text
     * module available for all languages.
     */
    public function setLanguageID(?int $languageID): static
    {
        $this->properties['languageID'] = $languageID;

        return $this;
    }

    /**
     * Sets the user who created the text module.
     */
    public function setUser(User $user): static
    {
        $this->properties['userID'] = $user->userID;
        $this->properties['username'] = $user->username;

        return $this;
    }

    public function setUserID(?int $userID): static
    {
        $this->properties['userID'] = $userID;

        return $this;
    }

    public function setUsername(string $username): static
    {
        $this->properties['username'] = $username;

        return $this;
    }

    #[\Override]
    protected function getRequiredProperties(): array
    {
        return ['title', 'message'];
    }
}
