<?php

namespace wcf\system\worker;

use wcf\data\object\type\ObjectType;
use wcf\system\WCF;

/**
 * Represents a worker that is registered with the RebuildWorkerCollecting event.
 *
 * @author Tim Duesterhus
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Worker
 * @since 6.0
 */
final class RegisteredWorker
{
    public function __construct(
        public readonly string $classname,
        private readonly ?ObjectType $legacyObjectType = null,
    ) {
    }

    public function getName(): string
    {
        if ($this->legacyObjectType !== null) {
            return WCF::getLanguage()->get(\sprintf(
                'wcf.acp.rebuildData.%s',
                $this->legacyObjectType->objectType
            ));
        }

        return WCF::getLanguage()->get(\sprintf(
            'wcf.acp.rebuildData.%s',
            \str_replace('\\', '_', $this->classname)
        ));
    }

    public function getDescription(): string
    {
        if ($this->legacyObjectType !== null) {
            return WCF::getLanguage()->get(\sprintf(
                'wcf.acp.rebuildData.%s.description',
                $this->legacyObjectType->objectType
            ));
        }

        return WCF::getLanguage()->get(\sprintf(
            'wcf.acp.rebuildData.%s.description',
            \str_replace('\\', '_', $this->classname)
        ));
    }
}
