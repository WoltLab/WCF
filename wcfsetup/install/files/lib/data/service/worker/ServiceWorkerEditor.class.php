<?php

namespace wcf\data\service\worker;

use wcf\data\DatabaseObjectEditor;

/**
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 *
 * @method  ServiceWorker     getDecoratedObject()
 * @method static ServiceWorker     create(array $parameters = [])
 * @mixin   ServiceWorker
 */
class ServiceWorkerEditor extends DatabaseObjectEditor
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = ServiceWorker::class;
}
