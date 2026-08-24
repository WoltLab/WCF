<?php

namespace wcf\command\unfurl\url;

use wcf\data\unfurl\url\UnfurlUrl;
use wcf\data\unfurl\url\UnfurlUrlBuilder;
use wcf\event\unfurl\url\UnfurlUrlCreated;
use wcf\system\event\EventHandler;

/**
 * Creates a new unfurl url.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class CreateUnfurlUrl
{
    public function __construct(
        private readonly UnfurlUrlBuilder $builder,
    ) {}

    public function __invoke(): UnfurlUrl
    {
        $unfurlUrl = $this->builder->create();

        EventHandler::getInstance()->fire(new UnfurlUrlCreated($unfurlUrl, $this->builder));

        return $unfurlUrl;
    }
}
