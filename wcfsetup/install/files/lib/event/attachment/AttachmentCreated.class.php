<?php

namespace wcf\event\attachment;

use wcf\data\attachment\Attachment;
use wcf\data\attachment\AttachmentBuilder;
use wcf\event\IPsr14Event;

/**
 * Indicates that an attachment has been created.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class AttachmentCreated implements IPsr14Event
{
    public function __construct(
        public readonly Attachment $attachment,
        public readonly AttachmentBuilder $builder,
    ) {}
}
