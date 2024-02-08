<?php

namespace wcf\page;

use wcf\system\rssFeed\RssFeed;
use wcf\system\rssFeed\RssFeedChannel;
use wcf\system\WCF;
use wcf\util\ArrayUtil;

/**
 * Generates RSS 2-Feeds.
 *
 * @author      Tim Duesterhus, Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
abstract class AbstractRssFeedPage extends AbstractAuthedPage
{
    /**
     * @inheritDoc
     */
    public $useTemplate = false;

    /**
     * parsed contents of $_REQUEST['id']
     * @var int[]
     */
    public array $objectIDs = [];

    #[\Override]
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

    #[\Override]
    public function show()
    {
        parent::show();
        if ($this->getPsr7Response()) {
            return;
        }

        $output = $this->getRssFeed()->render();

        @\header('Content-Type: application/rss+xml; charset=UTF-8');

        echo $output;
    }

    protected function getDefaultChannel(): RssFeedChannel
    {
        $channel = new RssFeedChannel();
        $channel
            ->title(WCF::getLanguage()->get(\PAGE_TITLE))
            ->description(WCF::getLanguage()->get(\PAGE_DESCRIPTION))
            ->link(WCF::getPath())
            ->language(WCF::getLanguage()->getFixedLanguageCode())
            ->pubDateFromTimestamp(\TIME_NOW)
            ->lastBuildDateFromTimestamp(\TIME_NOW)
            ->atomLinkSelf(WCF::getRequestURI());

        return $channel;
    }

    protected abstract function getRssFeed(): RssFeed;
}
