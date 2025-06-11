<?php

namespace wcf\system\moderation;

use wcf\data\DatabaseObjectList;

/**
 * Interface for deleted content provider.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated 6.2 Use `IDeletedContentListViewProvider` instead
 *
 * @template T of DatabaseObjectList
 */
interface IDeletedContentProvider
{
    /**
     * Returns a list of deleted content.
     *
     * @return T
     */
    public function getObjectList();

    /**
     * Returns the template name for the result output.
     *
     * @return string
     */
    public function getTemplateName();

    /**
     * Returns the application of the result template.
     *
     * @return string
     */
    public function getApplication();
}
