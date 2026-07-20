<?php

namespace wcf\command\tag;

use wcf\data\tag\Tag;
use wcf\data\tag\TagBuilder;
use wcf\event\tag\TagUpdated;
use wcf\system\event\EventHandler;

/**
 * Updates a tag.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UpdateTag
{
    public function __construct(
        private readonly TagBuilder $builder,
    ) {}

    public function __invoke(): Tag
    {
        $tag = $this->builder->update();

        EventHandler::getInstance()->fire(new TagUpdated($tag, $this->builder));

        return $tag;
    }
}
