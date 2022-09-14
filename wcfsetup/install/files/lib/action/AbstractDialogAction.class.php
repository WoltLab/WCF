<?php

namespace wcf\action;

use Laminas\Diactoros\Response\JsonResponse;
use wcf\system\exception\AJAXException;
use wcf\system\exception\IllegalLinkException;
use wcf\util\StringUtil;

/**
 * @deprecated 6.0 This class does not provide a useful implementation. The logic should be inlined.
 */
abstract class AbstractDialogAction extends AbstractSecureAction
{
    /**
     * current step
     * @var string
     */
    public $step = '';

    /**
     * template name
     * @var string
     */
    public $templateName = '';

    /**
     * response data
     * @var array
     */
    public $data = [];

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (empty($this->templateName)) {
            throw new IllegalLinkException();
        }

        if (isset($_REQUEST['step'])) {
            $this->step = StringUtil::trim($_REQUEST['step']);

            // append step as part of template name
            $this->templateName .= StringUtil::firstCharToUpperCase($this->step);
        }

        $this->validateStep();
    }

    /**
     * @inheritDoc
     */
    final public function execute()
    {
        parent::execute();

        $methodName = 'step' . StringUtil::firstCharToUpperCase($this->step);
        if (!\method_exists($this, $methodName)) {
            throw new AJAXException("Class '" . static::class . "' does not implement the required method '" . $methodName . "'");
        }

        // execute step
        $this->{$methodName}();

        $this->executed();

        return new JsonResponse($this->data);
    }

    /**
     * Validates current dialog step.
     */
    abstract protected function validateStep();
}
