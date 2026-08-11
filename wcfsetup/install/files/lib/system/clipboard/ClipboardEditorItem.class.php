<?php

namespace wcf\system\clipboard;

use wcf\system\exception\SystemException;

/**
 * Represents a clipboard item for inline editing.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class ClipboardEditorItem
{
    /**
     * internal data
     * @var mixed[]
     */
    private array $internalData = [];

    /**
     * item name
     */
    private string $name = '';

    /**
     * list of parameters passed to ClipboardProxyAction
     * @var mixed[]
     */
    private array $parameters = [];

    /**
     * redirect url
     */
    private string $url = '';

    /**
     * Returns internal data.
     *
     * @return mixed[]
     */
    public function getInternalData(): array
    {
        return $this->internalData;
    }

    /**
     * Returns item name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns parameters passed to ClipboardProxyAction.
     *
     * @return mixed[]
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Returns redirect url.
     */
    public function getURL(): string
    {
        return $this->url;
    }

    /**
     * Adds internal data, values will be left untouched by clipboard API.
     *
     * @throws SystemException
     */
    public function addInternalData(string $name, mixed $value): void
    {
        if (!\preg_match('~^[a-zA-Z]+$~', $name)) {
            throw new SystemException("internal data name '" . $name . "' is invalid");
        }

        if (\array_key_exists($name, $this->internalData)) {
            throw new SystemException("internal data name '" . $name . "' is not unique");
        }

        $this->internalData[$name] = $value;
    }

    /**
     * Adds an parameter passed to ClipboardProxyAction.
     *
     * @throws SystemException
     */
    public function addParameter(string $name, mixed $value): void
    {
        if (!\preg_match('~^[a-zA-Z]+$~', $name)) {
            throw new SystemException("parameter name '" . $name . "' is invalid");
        }

        if (\array_key_exists($name, $this->parameters)) {
            throw new SystemException("parameter name '" . $name . "' is not unique");
        }

        $this->parameters[$name] = $value;
    }

    /**
     * Sets item name.
     *
     * @throws SystemException
     */
    public function setName(string $name): void
    {
        if (!\preg_match('~^[a-zA-Z0-9\.-]+$~', $name)) {
            throw new SystemException("item name '" . $name . "' is invalid");
        }

        $this->name = $name;
    }

    /**
     * Sets redirect url, session id will be appended.
     */
    public function setURL(string $url): void
    {
        $this->url = $url;
    }

    /**
     * Returns number of affected items.
     */
    public function getCount(): int
    {
        if (isset($this->parameters['objectIDs'])) {
            return \count($this->parameters['objectIDs']);
        }

        return 0;
    }
}
