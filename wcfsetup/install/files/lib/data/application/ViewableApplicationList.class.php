<?php

namespace wcf\data\application;

/**
 * Represents a list of viewable applications.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  ViewableApplication     current()
 * @method  ViewableApplication[]       getObjects()
 * @method  ViewableApplication|null    getSingleObject()
 * @method  ViewableApplication|null    search($objectID)
 * @property    ViewableApplication[] $objects
 */
class ViewableApplicationList extends ApplicationList
{
    /**
     * @inheritDoc
     */
    public $decoratorClassName = ViewableApplication::class;
}
