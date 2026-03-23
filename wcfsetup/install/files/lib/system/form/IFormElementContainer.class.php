<?php

namespace wcf\system\form;

/**
 * Interface for form element containers.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
interface IFormElementContainer
{
    /**
     * Returns form element container description.
     *
     * @return string
     */
    public function getDescription();

    /**
     * Sets form element container description.
     *
     * @return void
     */
    public function setDescription(string $description);

    /**
     * Returns label.
     *
     * @return string
     */
    public function getLabel();

    /**
     * Sets label.
     *
     * @return void
     */
    public function setLabel(string $label);

    /**
     * Returns the value of child element with given name.
     *
     * @return mixed
     */
    public function getValue(string $key);

    /**
     * Returns a list of child elements.
     *
     * @return IFormElement[]
     */
    public function getChildren();

    /**
     * Appends a new child to stack.
     *
     * @param IFormElement $element
     * @return void
     */
    public function appendChild(IFormElement $element);

    /**
     * Prepends a new child to stack.
     *
     * @param IFormElement $element
     * @return void
     */
    public function prependChild(IFormElement $element);

    /**
     * Handles a POST or GET request.
     *
     * @param mixed[] $variables
     * @return void
     */
    public function handleRequest(array $variables);

    /**
     * Returns HTML-representation of current form element container.
     *
     * @return string
     */
    public function getHTML(string $formName);

    /**
     * Sets localized error message for named element.
     *
     * @return void
     */
    public function setError(string $name, string $error);
}
