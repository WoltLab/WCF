<?php
namespace wcf\system\user\notification\object\type;
use wcf\data\article\Article;
use wcf\data\article\ArticleList;
use wcf\system\user\notification\object\ArticleUserNotificationObject;

/**
 * Represents a gallery image as a notification object type.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Notification\Object\Type
 */
class ArticleUserNotificationObjectType extends AbstractUserNotificationObjectType {
	/**
	 * @inheritDoc
	 */
	protected static $decoratorClassName = ArticleUserNotificationObject::class;
	
	/**
	 * @inheritDoc
	 */
	protected static $objectClassName = Article::class;
	
	/**
	 * @inheritDoc
	 */
	protected static $objectListClassName = ArticleList::class;
}
