<?php

namespace wcf\acp\form;

use Laminas\Diactoros\Response\RedirectResponse;
use wcf\acp\action\FirstTimeSetupAction;
use wcf\data\option\Option;
use wcf\data\option\OptionAction;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\option\OptionHandler;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Shows general options during first time setup.
 *
 * @author Tim Duesterhus, Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 *
 * @phpstan-import-type ParsedOption from OptionHandler
 * @extends AbstractOptionListForm<OptionHandler>
 */
final class FirstTimeSetupOptionsForm extends AbstractOptionListForm
{
    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.configuration.canEditOption'];

    /**
     * list of options
     * @var ParsedOption[]
     */
    public array $options = [];

    /**
     * @var string[]
     */
    public array $optionNames = [
        'page_title',
        'page_description',
        'timezone',
    ];

    #[\Override]
    public function readParameters(): void
    {
        parent::readParameters();

        if (\FIRST_TIME_SETUP_STATE === -1) {
            throw new PermissionDeniedException();
        }
    }

    #[\Override]
    protected function initOptionHandler(): void
    {
        parent::initOptionHandler();

        $this->optionHandler->filterOptions($this->optionNames);
    }

    #[\Override]
    public function readData(): void
    {
        parent::readData();

        if ($this->hasPsr7Response()) {
            return;
        }

        foreach ($this->optionNames as $optionName) {
            $this->options[] = $this->optionHandler->getSingleOption($optionName);
        }
    }

    #[\Override]
    public function save(): void
    {
        parent::save();

        $saveOptions = $this->optionHandler->save('wcf.acp.option', 'wcf.acp.option.option');
        $saveOptions[Option::getOptionByName('first_time_setup_state')->optionID] = 2;
        $this->objectAction = new OptionAction([], 'updateAll', ['data' => $saveOptions]);
        $this->objectAction->executeAction();
        $this->saved();

        $this->setPsr7Response(new RedirectResponse(
            LinkHandler::getInstance()->getControllerLink(
                FirstTimeSetupAction::class,
            ),
            303
        ));
    }

    #[\Override]
    public function assignVariables(): void
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'options' => $this->options,
            'optionNames' => $this->optionNames,
        ]);
    }
}
