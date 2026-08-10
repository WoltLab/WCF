<?php

namespace wcf\acp\form;

use wcf\form\AbstractForm;
use wcf\system\exception\UserInputException;
use wcf\system\option\IOptionHandler;
use wcf\system\option\OptionHandler;

/**
 * This class provides default implementations for a list of options.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated  6.2 Use FormBuilder instead.
 *
 * @template-covariant TOptionHandler of IOptionHandler
 */
abstract class AbstractOptionListForm extends AbstractForm
{
    /**
     * @inheritDoc
     */
    public $errorField = [];

    /**
     * @inheritDoc
     */
    public $errorType = [];

    /**
     * name of the active option category
     * @var string
     */
    public $categoryName = '';

    /**
     * language item pattern
     * @var string
     */
    protected $languageItemPattern = '';

    /**
     * option handler object
     * @var ?TOptionHandler
     * @phpstan-ignore generics.variance
     */
    public $optionHandler;

    /**
     * option handler class name
     * @var string
     */
    public $optionHandlerClassName = OptionHandler::class;

    /**
     * true if option supports i18n
     * @var bool
     */
    public $supportI18n = true;

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        $this->optionHandler = new $this->optionHandlerClassName(
            $this->supportI18n,
            $this->languageItemPattern,
            $this->categoryName
        );
        $this->initOptionHandler();
    }

    /**
     * Initializes the option handler.
     *
     * @return void
     */
    protected function initOptionHandler()
    {
        $this->optionHandler->init();
    }

    #[\Override]
    public function readFormParameters()
    {
        parent::readFormParameters();

        $this->optionHandler->readUserInput($_POST);
    }

    #[\Override]
    public function validate()
    {
        $this->errorType = \array_merge($this->optionHandler->validate(), $this->errorType);

        parent::validate();

        if ($this->errorType !== []) {
            throw new UserInputException('options', $this->errorType);
        }
    }

    #[\Override]
    public function readData()
    {
        parent::readData();

        if ($_POST === []) {
            $this->optionHandler->readData();
        }
    }
}
