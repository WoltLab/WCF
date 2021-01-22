<?php

namespace wcf\system\form\builder\field\dependency;

use wcf\system\form\builder\field\IFormField;
use wcf\system\form\builder\IFormNode;

/**
 * Represents a dependency of one node on (the value of) a field.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Form\Builder\Field\Dependency
 * @since   5.2
 */
interface IFormFieldDependency
{
    /**
     * Returns `true` if the dependency is met, thus if the dependant form field should
     * be considered available. Otherwise, `false` is returned.
     *
     * @return  bool
     */
    public function checkDependency();

    /**
     * Sets the node whose availability depends on the value of a field.
     *
     * @param IFormNode $node dependent node
     * @return  static              this dependency
     *
     * @throws  \BadMethodCallException     if no dependent node has been set
     */
    public function dependentNode(IFormNode $node);

    /**
     * Sets the field the availability of the node dependents on.
     *
     * @param IFormField $field field
     * @return  static              this dependency
     *
     * @throws  \BadMethodCallException     if no field has been set
     */
    public function field(IFormField $field);

    /**
     * Sets the id of the field the availability of the node dependents on.
     *
     * This method should only be used before building the form as afterwards,
     * the actual field is no automatically set.
     *
     * @param string $fieldId field id
     * @return  static              this dependency
     *
     * @throws  \BadMethodCallException     if the field has already been set
     */
    public function fieldId($fieldId);

    /**
     * Returns the node whose availability depends on the value of a field.
     *
     * @return  IFormNode   dependent node
     */
    public function getDependentNode();

    /**
     * Returns the field the availability of the element dependents on or `null` if the field has
     * not been set yet.
     *
     * @return  IFormField  field controlling element availability
     */
    public function getField();

    /**
     * Returns the id of the field the availability of the node dependents on;
     *
     * @return  string
     * @throws  \BadMethodCallException     if neither field not field id has been set
     */
    public function getFieldId();

    /**
     * Returns the JavaScript code required to ensure this dependency in the template.
     *
     * @return  string      dependency JavaScript code
     */
    public function getHtml();

    /**
     * Returns the id of this dependency.
     *
     * @return  string      id of the dependency
     */
    public function getId();

    /**
     * Creates a new dependency with the given id.
     *
     * @param string $id id of the created dependency
     * @return  static              newly created dependency
     *
     * @throws  \InvalidArgumentException   if the given id is invalid
     */
    public static function create($id);
}
