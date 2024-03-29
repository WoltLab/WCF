<?php

namespace wcf\system\moderation;

use wcf\data\object\type\AbstractObjectTypeProcessor;

/**
 * Abstract implementation of a deleted content provider.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
abstract class AbstractDeletedContentProvider extends AbstractObjectTypeProcessor implements IDeletedContentProvider
{
    /**
     * @inheritDoc
     */
    public function getApplication()
    {
        $classParts = \explode('\\', static::class);

        return $classParts[0];
    }
}
