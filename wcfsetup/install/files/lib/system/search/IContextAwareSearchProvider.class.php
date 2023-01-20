<?php

namespace wcf\system\search;

/**
 * Interface for full-text search providers that provide
 * additional context information for messages.
 *
 * CAUTION: This is an experimental API that is not designed
 *          for general consumption.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
interface IContextAwareSearchProvider extends ISearchProvider
{
    /**
     * Returns the context filter that is being applied
     * to the inner search query.
     */
    public function getContextFilter(array $parameters): array;
}
