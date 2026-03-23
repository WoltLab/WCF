<?php

namespace wcf\system\form;

/**
 * Interface for form elements.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
interface IFormElement
{
    public function __construct(IFormElementContainer $parent);

    /**
     * Returns form element description.
     *
     * @return string
     */
    public function getDescription();

    /**
     * Sets form element description.
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
     * Returns element's parent container element.
     *
     * @return IFormElementContainer
     */
    public function getParent();

    /**
     * Returns HTML-representation of current form element.
     *
     * @return string
     */
    public function getHTML(string $formName);

    /**
     * Sets localized error message.
     *
     * @return void
     */
    public function setError(string $error);

    /**
     * Returns localized error message, empty if no error occurred.
     *
     * @return string
     */
    public function getError();
}
