# Changelog

## 2.2 (Vortex)

### 2.2.0 Alpha 1 (XXXX-YY-ZZ)

* Clipboard support for tags in ACP ("delete" and "set as synonyms").
* `wcf\data\user\UserProfileCache` for caching user profiles during runtime.
* `wcf\system\cache\builder\EventListenerCacheBuilder` returns `wcf\data\event\listener\EventListener` objects instead of data arrays.
* `wcf\system\cache\source\RedisCacheSource` added.
* Background queue (`wcf\system\background\*`) added.
* Rewritten email system (`wcf\system\email\*`) added.
* CryptoUtil (`wcf\util\CryptoUtil`) added.
* Old email system (`wcf\system\mail\*`) deprecated.
* Abstract bulk processing system added.
* Replaced old user bulk processing with new implementation using the abstract bulk processing system.
* `conditionContainers` template event in template `noticeAdd.tpl` added.
* Use condition system for user search.
* Image proxy for images included with the image BBCode.
* Overhauled Redactor integration
	* Linebreaks mode instead of using paragraphs, works better with the PHP-side parser which works with linebreaks
	* Ported the PHP-BBCode parser, massively improves accuracy and ensures validity
* Show error message if poll options are given but not question instead of discarding poll options.
* `parentObjectID` column added to `modification_log` and `wcf\system\log\modification\AbstractModificationLogHandler` introduced as a replacement for `wcf\system\log\modification\ModificationLogHandler`.
* Add sort support for `useroptions` option type.
* Make user options shown in sidebar sortable.
* `wcf\system\event\listener\AbstractUserActionRenameListener` added.
* `wcf\system\event\listener\AbstractUserMergeListener` added.
* Notice texts support `{$username}` and `{$email}` placeholders.
* Notifications for comments in moderation.
* Continuous numeration of edit history version in template.
* `\wcf\data\user\UserProfile::getGuestUserProfile()` added.
* Make labels sortable in ACP.

#### CMS

* User online location is handled via the `wcf\data\page\Page` objects. Static locations only need a language item `wcf.page.onlineLocation.{$page->identifier}`, more complex locations can use the online location-related methods of `wcf\system\page\handler\IMenuPageHandler`. For CMS pages, their title is used.

#### New Traits

* `wcf\data\TDatabaseObjectOptions` for database object-bound options validation.
* `wcf\data\TDatabaseObjectPermissions` for database object-bound permissions validation.
* `wcf\data\TMultiCategoryObject` provides category-related methods for objects with multiple categories.
* `wcf\data\TUserContent` provides default implementations of the (non-inherited) methods of the IUserContent interface.

#### Package Installation Plugin Improvements

* instruction file name for most PIPs has default value provided by `wcf\system\package\plugin\IPackageInstallationPlugin::getDefaultFilename()`.
* `options` support for cronjob PIP.
* `name` attribute for cronjob PIP (`cronjobName` for cronjob objects).
* `eventName` of event listener PIP supports multiple events.
* `permissions` and `options` support for event listener PIP.
* `name` attribute for event listener PIP (`listenerName` for event listener objects).
* `permissions` and `options` support for template listener PIP.
* file `{WCF_DIR}/acp/uninstall/{packageName}.php` is automatically executed if package is uninstalled right before the first file PIP is executed

#### Removed Code

* `wcf\system\clipboard\action\UserExtendedClipboardAction` removed.
* `wcf\system\event\listener\PreParserAtUserListener` removed.
* `wcf\action\AJAXProxyAction::getData()` removed.
* Version system removed.
* Support for query string based sessions in Frontend removed.
* Language server system removed.
* Deprecated methods in `wcf\util\StringUtil` removed.

#### Documentation

* `@property-read` tags for database table columns of classes extending `wcf\data\DatabaseObject`.
