<?php

namespace wcf\system\version;

/**
 * Generic data holder for version tracker entries.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   int $versionID      unique id of the tracked version entry
 * @property-read   int $objectID       id of the edited object
 * @property-read   int|null $userID         id of the user who has created the previous version of the object or `null` if the user does not exist anymore or if the previous version has been created by a guest
 * @property-read   string $username       name of the user who has created the previous version of the object
 * @property-read   int $time           timestamp at which the original version has been created
 */
class VersionTrackerEntry
{
    /**
     * object data
     * @var mixed[]
     */
    protected $data = [];

    /**
     * list of stored properties and their values
     * @var mixed[]
     */
    protected $payload = [];

    /**
     * @param mixed[] $data version data
     */
    public function __construct(?int $id, array $data)
    {
        if ($id !== null) {
            throw new \InvalidArgumentException("Accessing tracked versions by id is not supported.");
        }

        if (isset($data['data'])) {
            $payload = (\is_array($data['data'])) ? $data['data'] : @\unserialize($data['data']);
            if ($payload !== false && \is_array($payload)) {
                $this->payload = $payload;
            }

            unset($data['data']);
        }

        $this->data = $data;
    }

    /**
     * Returns the value of a object data variable with the given name or `null` if no
     * such data variable exists.
     *
     * @return  mixed
     */
    public function __get(string $name)
    {
        if (isset($this->data[$name])) {
            return $this->data[$name];
        } else {
            return;
        }
    }

    /**
     * Determines if the object data variable with the given name is set and
     * is not NULL.
     *
     * @return  bool
     */
    public function __isset(string $name)
    {
        return isset($this->data[$name]);
    }

    /**
     * Returns the stored value of a property or null if unknown.
     *
     * @return      string
     */
    public function getPayload(string $property, int $languageID)
    {
        if (isset($this->payload[$languageID])) {
            return (isset($this->payload[$languageID][$property])) ? $this->payload[$languageID][$property] : '';
        }

        return '';
    }

    /**
     * Returns the stored values for all given properties. Unknown or missing
     * properties will be set to an empty string.
     *
     * @param string[] $properties list of property names
     * @return      string[]
     */
    public function getPayloadForProperties(array $properties, int $languageID)
    {
        $payload = [];
        foreach ($properties as $property) {
            $payload[$property] = '';

            if (isset($this->payload[$languageID]) && isset($this->payload[$languageID][$property])) {
                $payload[$property] = $this->payload[$languageID][$property];
            }
        }

        return $payload;
    }

    /**
     * Returns the list of language ids.
     *
     * @return      int[]
     */
    public function getLanguageIDs()
    {
        return \array_keys($this->payload);
    }
}
