<?php

namespace wcf\page;

use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\HeaderUtil;

/**
 * Generates RSS 2-Feeds.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated 6.1 use `AbstractRssFeedPage` instead
 */
abstract class AbstractFeedPage extends AbstractAuthedPage
{
    /**
     * @inheritDoc
     */
    public $templateName = 'rssFeed';

    /**
     * application name
     * @var string
     */
    public $application = 'wcf';

    /**
     * @inheritDoc
     */
    public $useTemplate = false;

    /**
     * parsed contents of $_REQUEST['id']
     * @var int[]
     */
    public $objectIDs = [];

    /**
     * list of feed-entries for the current page object must be an \Iterator with \wcf\data\IFeedEntry-Elements
     * @var \Iterator
     */
    public $items;

    /**
     * feed title
     * @var string
     */
    public $title = '';

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'items' => $this->items,
            'title' => $this->title,
            'supportsEnclosure' => false,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (isset($_REQUEST['id'])) {
            if (\is_array($_REQUEST['id'])) {
                // ?id[]=1337&id[]=9001
                $this->objectIDs = ArrayUtil::toIntegerArray($_REQUEST['id']);
            } else {
                // ?id=1337 or ?id=1337,9001
                $this->objectIDs = ArrayUtil::toIntegerArray(\explode(',', $_REQUEST['id']));
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function show()
    {
        parent::show();
        if ($this->getPsr7Response()) {
            return;
        }

        // set correct content-type
        @\header('Content-Type: application/rss+xml; charset=UTF-8');

        // show template
        WCF::getTPL()->display($this->templateName, $this->application, false);
    }

    protected function redirectToNewPage(string $className): void
    {
        $parameters = [];
        $url = '';
        if ($this->objectIDs !== []) {
            if (\count($this->objectIDs) === 1) {
                $parameters['id'] = \reset($this->objectIDs);
            } else {
                $url = 'id=' . \implode(',', $this->objectIDs);
            }
        }
        if (isset($_REQUEST['at'])) {
            $parameters['at'] = $_REQUEST['at'];
        }
        HeaderUtil::redirect(
            LinkHandler::getInstance()->getControllerLink($className, $parameters, $url),
            true,
            false
        );
        exit;
    }
}
