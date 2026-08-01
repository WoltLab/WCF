<?php

namespace wcf\acp\form;

use wcf\data\option\category\OptionCategory;
use wcf\data\option\OptionAction;
use wcf\http\Helper;
use wcf\system\application\ApplicationHandler;
use wcf\system\cache\builder\ApiEndpointCacheBuilder;
use wcf\system\exception\IllegalLinkException;
use wcf\system\menu\acp\ACPMenu;
use wcf\system\option\OptionHandler;
use wcf\system\service\worker\ServiceWorkerHandler;
use wcf\system\style\StyleHandler;
use wcf\system\WCF;

/**
 * Shows the option edit form.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractOptionListForm<OptionHandler>
 */
class OptionForm extends AbstractOptionListForm
{
    /**
     * category option
     * @var OptionCategory
     */
    public $category;

    /**
     * the option tree
     * @var mixed[]
     */
    public $optionTree = [];

    /**
     * @inheritDoc
     */
    protected $languageItemPattern = 'wcf.acp.option.option\d+';

    #[\Override]
    public function readParameters()
    {
        $this->category = Helper::fetchObjectFromQueryParameter(OptionCategory::class);
        $this->categoryName = $this->category->categoryName;

        parent::readParameters();
    }

    #[\Override]
    public function save()
    {
        parent::save();

        // save options
        $saveOptions = $this->optionHandler->save('wcf.acp.option', 'wcf.acp.option.option');
        $this->objectAction = new OptionAction([], 'updateAll', ['data' => $saveOptions]);
        $this->objectAction->executeAction();
        $this->saved();

        ServiceWorkerHandler::getInstance()->updateKeys();

        // reset styles to make sure the updated option values are used
        StyleHandler::resetStylesheets();

        // Reset the cached list of API endpoints to make it possible to
        // conditionally enable them through options only.
        ApiEndpointCacheBuilder::getInstance()->reset();

        // show success message
        WCF::getTPL()->assign('success', true);
    }

    #[\Override]
    public function readData()
    {
        parent::readData();

        // load option tree
        $this->optionTree = $this->optionHandler->getOptionTree($this->category->categoryName);

        if (empty($_POST)) {
            // not a valid top (level 1 or 2) category
            if (!isset($this->optionTree[0])) {
                throw new IllegalLinkException();
            }
        }
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'category' => $this->category,
            'optionTree' => $this->optionTree,
            'rewriteTestApplications' => ApplicationHandler::getInstance()->getApplications(),
        ]);
    }

    #[\Override]
    public function show()
    {
        // set active menu item
        ACPMenu::getInstance()->setActiveMenuItem('wcf.acp.option.category.' . $this->category->categoryName);

        // check permission
        WCF::getSession()->checkPermissions(['admin.configuration.canEditOption']);

        // show form
        parent::show();
    }
}
