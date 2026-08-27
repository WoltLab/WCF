<?php

namespace wcf\command\attachment;

use wcf\data\attachment\Attachment;
use wcf\data\attachment\AttachmentBuilder;
use wcf\event\attachment\AttachmentCreated;
use wcf\system\event\EventHandler;

/**
 * Creates a new attachment.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class CreateAttachment
{
    public function __construct(
        private readonly AttachmentBuilder $builder,
    ) {}

    public function __invoke(): Attachment
    {
        $attachment = $this->builder->create();

        EventHandler::getInstance()->fire(new AttachmentCreated($attachment, $this->builder));

        return $attachment;
    }
}
