<?php

namespace wcf\data\application;

/**
 * Represents a list of viewable applications.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated 6.3 Use `ApplicationList` instead.
 *
 * @extends ApplicationList<ViewableApplication>
 */
class ViewableApplicationList extends ApplicationList
{
    /**
     * @inheritDoc
     */
    public $decoratorClassName = ViewableApplication::class;
}
