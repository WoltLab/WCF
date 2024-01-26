<?php

namespace wcf\system\form\builder;

use wcf\system\application\ApplicationHandler;
use wcf\system\WCF;

/**
 * Form node that shows the contents of a specific template.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
class TemplateFormNode implements IFormChildNode
{
    use TFormChildNode;
    use TFormNode;

    /**
     * application of the template with the contents of the form node
     * @var null|string
     */
    protected $application;

    /**
     * name of the template with the contents of the form node
     * @var null|string
     */
    protected $templateName;

    /**
     * template variables passed to the template
     * @var array
     */
    protected $variables = [];

    /**
     * Sets the application of the template with the contents of the form node and returns this
     * form node.
     *
     * @param string $application application abbreviation
     * @return  static              this form node
     *
     * @throws  \InvalidArgumentException   if no application with the given abbreviation exists
     */
    public function application($application)
    {
        if (ApplicationHandler::getInstance()->getApplication($application) === null) {
            throw new \InvalidArgumentException(
                "Unknown application with abbreviation '{$application}' for node '{$this->getId()}'."
            );
        }

        $this->application = $application;

        return $this;
    }

    /**
     * Returns the application of the template with the contents of the form node.
     *
     * If no application has been set, `wcf` will be set and return.
     *
     * @return  string      application abbreviation
     */
    public function getApplication()
    {
        if ($this->application === null) {
            $this->application = 'wcf';
        }

        return $this->application;
    }

    /**
     * @inheritDoc
     */
    public function getHtml()
    {
        return WCF::getTPL()->fetch(
            $this->getTemplateName(),
            $this->getApplication(),
            $this->getVariables(),
            true
        );
    }

    /**
     * Returns the name of the template with the contents of the form node.
     *
     * @return  string              name of template with form node contents
     *
     * @throws  \BadMethodCallException     if template name has not been set yet
     */
    public function getTemplateName()
    {
        if ($this->templateName === null) {
            throw new \BadMethodCallException(
                "Template name has not been set yet for node '{$this->getId()}'."
            );
        }

        return $this->templateName;
    }

    /**
     * Returns the template variables passed to the template.
     *
     * @return  array           template variables passed to template
     */
    public function getVariables()
    {
        return $this->variables;
    }

    /**
     * Sets the name of the template with the contents of the form node and returns this form node.
     *
     * @param string $templateName name of template with form node contents
     * @return  static              this form node
     */
    public function templateName($templateName)
    {
        $this->templateName = $templateName;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        // does nothing
    }

    /**
     * Sets the template variables passed to the template and returns this form node.
     *
     * @param array $variables template variables passed to template
     * @return  static          this form node
     */
    public function variables(array $variables)
    {
        $this->variables = $variables;

        return $this;
    }
}
