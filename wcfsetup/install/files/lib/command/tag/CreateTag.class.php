<?php

namespace wcf\command\tag;

use wcf\data\tag\Tag;
use wcf\data\tag\TagBuilder;
use wcf\event\tag\TagCreated;
use wcf\system\event\EventHandler;

/**
 * Creates a new tag.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class CreateTag
{
    public function __construct(
        private readonly TagBuilder $builder,
    ) {}

    public function __invoke(): Tag
    {
        $tag = $this->builder->create();

        EventHandler::getInstance()->fire(new TagCreated($tag, $this->builder));

        return $tag;
    }
}
