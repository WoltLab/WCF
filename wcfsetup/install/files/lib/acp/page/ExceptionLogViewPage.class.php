<?php

namespace wcf\acp\page;

use wcf\page\AbstractPage;
use wcf\page\MultipleLinkPage;
use wcf\system\event\EventHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\Regex;
use wcf\system\registry\RegistryHandler;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\DirectoryUtil;
use wcf\util\ExceptionLogUtil;
use wcf\util\StringUtil;

/**
 * Shows the exception log.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Acp\Page
 */
class ExceptionLogViewPage extends MultipleLinkPage
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.log.exception';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.management.canViewLog'];

    /**
     * @inheritDoc
     */
    public $itemsPerPage = 10;

    /**
     * given exceptionID
     * @var string
     */
    public $exceptionID = '';

    /**
     * @inheritDoc
     */
    public $forceCanonicalURL = true;

    /**
     * active logfile
     * @var string
     */
    public $logFile = '';

    /**
     * available logfiles
     * @var string[]
     */
    public $logFiles = [];

    /**
     * exceptions shown
     * @var array
     */
    public $exceptions = [];

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (isset($_REQUEST['exceptionID'])) {
            $this->exceptionID = StringUtil::trim($_REQUEST['exceptionID']);
        }
        if (isset($_REQUEST['logFile'])) {
            $this->logFile = StringUtil::trim($_REQUEST['logFile']);
        }

        $parameters = [];
        if ($this->exceptionID !== '') {
            $parameters['exceptionID'] = $this->exceptionID;
        } elseif ($this->logFile !== '') {
            $parameters['logFile'] = $this->logFile;
        }

        $this->canonicalURL = LinkHandler::getInstance()->getControllerLink(self::class, $parameters);
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        AbstractPage::readData();

        // mark notifications as read
        RegistryHandler::getInstance()->set('com.woltlab.wcf', 'exceptionMailerTimestamp', TIME_NOW);

        $fileNameRegex = new Regex('(?:^|/)\d{4}-\d{2}-\d{2}\.txt$');
        $this->logFiles = DirectoryUtil::getInstance(WCF_DIR . 'log/', false)->getFiles(\SORT_DESC, $fileNameRegex);

        if ($this->exceptionID) {
            // search the appropriate file
            foreach ($this->logFiles as $logFile) {
                $contents = \file_get_contents($logFile);

                if (\mb_strpos($contents, '<<<<<<<<' . $this->exceptionID . '<<<<') !== false) {
                    $fileNameRegex->match($logFile);
                    $matches = $fileNameRegex->getMatches();
                    $this->logFile = $matches[0];
                    break;
                }

                unset($contents);
            }

            if (!isset($contents)) {
                $this->logFile = '';

                return;
            }
        } elseif ($this->logFile) {
            if (!$fileNameRegex->match(\basename($this->logFile))) {
                throw new IllegalLinkException();
            }
            if (!\file_exists(WCF_DIR . 'log/' . $this->logFile)) {
                throw new IllegalLinkException();
            }

            $contents = \file_get_contents(WCF_DIR . 'log/' . $this->logFile);
        } else {
            return;
        }

        try {
            $this->exceptions = ExceptionLogUtil::splitLog($contents);
        } catch (\Exception $e) {
            return;
        }

        // show latest exceptions first
        $this->exceptions = \array_reverse($this->exceptions, true);

        if ($this->exceptionID) {
            $this->searchPage($this->exceptionID);
        }
        $this->calculateNumberOfPages();

        $i = 0;
        $seenHashes = [];
        foreach ($this->exceptions as $key => $val) {
            $i++;

            $parsed = ExceptionLogUtil::parseException($val);
            if (isset($seenHashes[$parsed['stackHash']])) {
                $parsed['collapsed'] = true;
            }
            $seenHashes[$parsed['stackHash']] = true;

            if ($i < $this->startIndex || $i > $this->endIndex) {
                unset($this->exceptions[$key]);
                continue;
            }
            try {
                $this->exceptions[$key] = $parsed;
            } catch (\InvalidArgumentException $e) {
                unset($this->exceptions[$key]);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function countItems()
    {
        // call countItems event
        EventHandler::getInstance()->fireAction($this, 'countItems');

        return \count($this->exceptions);
    }

    /**
     * Switches to the page containing the exception with the given ID.
     *
     * @param string $exceptionID
     */
    public function searchPage($exceptionID)
    {
        $i = 1;

        foreach ($this->exceptions as $key => $val) {
            if ($key == $exceptionID) {
                break;
            }
            $i++;
        }

        $this->pageNo = \ceil($i / $this->itemsPerPage);
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'exceptionID' => $this->exceptionID,
            'logFiles' => \array_flip(\array_map('basename', $this->logFiles)),
            'logFile' => $this->logFile,
            'exceptions' => $this->exceptions,
        ]);
    }
}
