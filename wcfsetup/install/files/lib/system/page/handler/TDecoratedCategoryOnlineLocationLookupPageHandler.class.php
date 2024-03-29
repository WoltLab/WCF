<?php

namespace wcf\system\page\handler;

/**
 * Implementation of the `IOnlineLocationPageHandler` and `ILookupPageHandler` interfaces
 * and implementing the `IMenuPageHandler::isVisible()` method..
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 */
trait TDecoratedCategoryOnlineLocationLookupPageHandler
{
    use TDecoratedCategoryLookupPageHandler;
    use TDecoratedCategoryMenuPageHandler;
    use TDecoratedCategoryOnlineLocationPageHandler;
}
