<?php

namespace wcf\command\unfurl\url;

use wcf\data\unfurl\url\UnfurlUrl;
use wcf\data\unfurl\url\UnfurlUrlBuilder;
use wcf\system\background\BackgroundQueueHandler;
use wcf\system\background\job\UnfurlUrlBackgroundJob;

/**
 * Returns the unfurl url object for a given url.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class FindOrCreateUnfurlUrl
{
    public const REFETCH_UNFURL_URL = 86_400;

    public function __construct(
        private readonly string $url,
    ) {}

    public function __invoke(): UnfurlUrl
    {
        $object = UnfurlUrl::getByUrl($this->url);

        if ($object === null) {
            $object = new CreateUnfurlUrl(UnfurlUrlBuilder::forCreate()
                ->setUrl($this->url))();
        }

        if ($object->status !== UnfurlUrl::STATUS_PENDING && $object->lastFetch < \TIME_NOW - self::REFETCH_UNFURL_URL) {
            BackgroundQueueHandler::getInstance()->enqueueIn([
                new UnfurlUrlBackgroundJob($object),
            ]);

            BackgroundQueueHandler::getInstance()->forceCheck();
        }

        return $object;
    }
}
