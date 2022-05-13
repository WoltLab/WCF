<?php

namespace wcf\acp\form;

use Laminas\Diactoros\Uri;
use wcf\data\package\update\server\PackageUpdateServerAction;
use wcf\data\package\update\server\PackageUpdateServerList;
use wcf\form\AbstractForm;
use wcf\system\exception\UserInputException;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows the server add form.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Acp\Form
 */
class PackageUpdateServerAddForm extends AbstractForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.package.server.add';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.configuration.package.canEditServer'];

    /**
     * server url
     * @var string
     */
    public $serverURL = '';

    /**
     * server login username
     * @var string
     */
    public $loginUsername = '';

    /**
     * server login password
     * @var string
     */
    public $loginPassword = '';

    /**
     * @inheritDoc
     */
    public function readFormParameters()
    {
        parent::readFormParameters();

        if (isset($_POST['serverURL'])) {
            $this->serverURL = StringUtil::trim($_POST['serverURL']);
        }
        if (isset($_POST['loginUsername'])) {
            $this->loginUsername = $_POST['loginUsername'];
        }
        if (isset($_POST['loginPassword'])) {
            $this->loginPassword = $_POST['loginPassword'];
        }
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        parent::validate();

        $this->validateServerURL();
    }

    /**
     * Validates the server URL.
     *
     * @since       5.3
     */
    protected function validateServerURL()
    {
        if (empty($this->serverURL)) {
            throw new UserInputException('serverURL');
        }

        try {
            $url = new Uri($this->serverURL);
            $this->serverURL = (string)$url;

            if (!$url->getHost()) {
                throw new UserInputException('serverURL', 'invalid');
            }
            if ($url->getHost() !== 'localhost') {
                if ($url->getScheme() !== 'https') {
                    throw new UserInputException('serverURL', 'invalidScheme');
                }
                if ($url->getPort()) {
                    throw new UserInputException('serverURL', 'nonStandardPort');
                }
            }
            if ($url->getUserInfo()) {
                throw new UserInputException('serverURL', 'userinfo');
            }
            if (\str_ends_with(\strtolower($url->getHost()), '.woltlab.com')) {
                throw new UserInputException('serverURL', 'woltlab');
            }
        } catch (\InvalidArgumentException) {
            throw new UserInputException('serverURL', 'invalid');
        }

        if (($duplicate = $this->findDuplicateServer())) {
            throw new UserInputException('serverURL', [
                'duplicate' => $duplicate,
            ]);
        }
    }

    /**
     * Returns the first package update server with a matching serverURL.
     *
     * @since       5.3
     */
    protected function findDuplicateServer()
    {
        $packageServerList = new PackageUpdateServerList();
        $packageServerList->readObjects();
        foreach ($packageServerList as $packageServer) {
            if ($packageServer->serverURL == $this->serverURL) {
                return $packageServer;
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        parent::save();

        // save server
        $this->objectAction = new PackageUpdateServerAction([], 'create', [
            'data' => \array_merge($this->additionalFields, [
                'serverURL' => $this->serverURL,
                'loginUsername' => $this->loginUsername,
                'loginPassword' => $this->loginPassword,
            ]),
        ]);
        $returnValues = $this->objectAction->executeAction();
        $this->saved();

        // reset values
        $this->serverURL = $this->loginUsername = $this->loginPassword = '';

        // show success message
        WCF::getTPL()->assign([
            'success' => true,
            'objectEditLink' => LinkHandler::getInstance()->getControllerLink(
                PackageUpdateServerEditForm::class,
                ['id' => $returnValues['returnValues']->packageUpdateServerID]
            ),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'serverURL' => $this->serverURL,
            'loginUsername' => $this->loginUsername,
            'loginPassword' => $this->loginPassword,
            'action' => 'add',
        ]);
    }
}
