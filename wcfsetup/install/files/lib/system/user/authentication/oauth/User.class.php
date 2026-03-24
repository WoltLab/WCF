<?php

namespace wcf\system\user\authentication\oauth;

/**
 * Represents user information retrieved from an OAuth provider.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.4
 * @implements \ArrayAccess<string, string|array<string, string>>
 */
final class User implements \ArrayAccess
{
    /**
     * @var array<string, string|array<string, string>>
     */
    private array $data = [];

    /**
     * @param array<string, string|array<string, string>> $data
     */
    public function __construct(array $data)
    {
        if (empty($data['__id'])) {
            throw new \InvalidArgumentException("Missing '__id' key");
        }
        if (empty($data['__username'])) {
            throw new \InvalidArgumentException("Missing '__username' key");
        }

        $this->data = $data;
    }

    /**
     * Returns the unique identifier for this user at the OAuth provider.
     */
    public function getId(): string
    {
        return $this['__id'];
    }

    /**
     * Returns what the user considers their "username" or "handle" at
     * the OAuth provider.
     *
     * Depending on the provider this might be a real name, a handle or
     * something entirely different.
     */
    public function getUsername(): string
    {
        return $this['__username'];
    }

    /**
     * Returns the user's email at the OAuth provider.
     *
     * Some providers might not return an email, so this method may
     * return null.
     */
    public function getEmail(): ?string
    {
        return $this['__email'] ?? null;
    }

    #[\Override]
    public function offsetGet($offset): mixed
    {
        return $this->data[$offset] ?? null;
    }

    #[\Override]
    public function offsetSet($offset, $value): void
    {
        if ($offset === '__id') {
            throw new \BadMethodCallException('You may not modify the id.');
        }
        if ($offset === '__username') {
            throw new \BadMethodCallException('You may not modify the username.');
        }

        $this->data[$offset] = $value;
    }

    #[\Override]
    public function offsetUnset($offset): void
    {
        if ($offset === '__id') {
            throw new \BadMethodCallException('You may not modify the id.');
        }
        if ($offset === '__username') {
            throw new \BadMethodCallException('You may not modify the username.');
        }

        unset($this->data[$offset]);
    }

    #[\Override]
    public function offsetExists($offset): bool
    {
        return isset($this->data[$offset]);
    }
}
