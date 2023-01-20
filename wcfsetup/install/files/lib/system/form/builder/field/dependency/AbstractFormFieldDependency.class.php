<?php

namespace wcf\system\form\builder\field\dependency;

use wcf\system\application\ApplicationHandler;
use wcf\system\form\builder\field\IFormField;
use wcf\system\form\builder\IFormNode;
use wcf\system\WCF;

/**
 * Abstract implementation of a form field dependency.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
abstract class AbstractFormFieldDependency implements IFormFieldDependency
{
    /**
     * node whose availability depends on the value of a field
     * @var IFormNode
     */
    protected $dependentNode;

    /**
     * field the availability of the node dependents on
     * @var IFormField
     */
    protected $field;

    /**
     * id of the field the availability of the node dependents on
     * @var string
     */
    protected $fieldId;

    /**
     * id of the dependency
     * @var string
     */
    protected $id;

    /**
     * name of the template containing the dependency JavaScript code
     * @var null|string
     */
    protected $templateName;

    /**
     * abbreviation of the application the template belongs to
     * @var string
     * @since   5.4
     */
    protected $templateNameApplication = 'wcf';

    /**
     * @inheritDoc
     */
    public function dependentNode(IFormNode $node)
    {
        $this->dependentNode = $node;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function field(IFormField $field)
    {
        $this->field = $field;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function fieldId($fieldId)
    {
        if ($this->getField() !== null) {
            throw new \BadMethodCallException("Cannot set field id after field has been set.");
        }

        $this->fieldId = $fieldId;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getDependentNode()
    {
        if ($this->dependentNode === null) {
            throw new \BadMethodCallException("Dependent node has not been set.");
        }

        return $this->dependentNode;
    }

    /**
     * @inheritDoc
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @inheritDoc
     */
    public function getFieldId()
    {
        if ($this->getField() !== null) {
            return $this->getField()->getId();
        }

        if ($this->fieldId === null) {
            throw new \BadMethodCallException("Neither the field nor the field id has been set.");
        }

        return $this->fieldId;
    }

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function getHtml()
    {
        if ($this->templateName === null) {
            throw new \LogicException("Template name is not set.");
        }
        if (ApplicationHandler::getInstance()->getApplication($this->templateNameApplication) === null) {
            throw new \LogicException("Unknown application with abbreviation '{$this->templateNameApplication}'.");
        }

        return WCF::getTPL()->fetch($this->templateName, $this->templateNameApplication, [
            'dependency' => $this,
        ], true);
    }

    /**
     * Sets the id of this dependency and returns this dependency.
     *
     * @param string $id id of the dependency
     * @return  static      $this       this dependency
     *
     * @throws  \InvalidArgumentException   if given id no or otherwise invalid
     */
    protected function id($id)
    {
        if (\preg_match('~^[a-z][A-z0-9-]*$~', $id) !== 1) {
            throw new \InvalidArgumentException("Invalid id '{$id}' given.");
        }

        $this->id = $id;

        return $this;
    }

    /**
     * Creates a new dependency with the given id.
     *
     * @param string $id id of the created dependency
     * @return  static              newly created dependency
     *
     * @throws  \InvalidArgumentException   if the given id is invalid
     */
    public static function create($id)
    {
        return (new static())->id($id);
    }
}
