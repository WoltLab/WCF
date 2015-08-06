# Changelog

## 2.2 (Vortex)

### 2.2.0 Alpha 1 (XXXX-YY-ZZ)

* Clipboard support for tags in ACP ("delete" and "set as synonyms").
* `wcf\data\user\UserProfileCache` for caching user profiles during runtime.
* instruction file name for most PIPs has default value provided by `wcf\system\package\plugin\IPackageInstallationPlugin::getDefaultFilename()`.
* `options` support for cronjobs.
* `name` attribute for cronjob PIP (`cronjobName` for cronjob objects).
* `eventName` of event listener PIP supports multiple events.
* `permissions` and `options` support for event listeners.
* `name` attribute for event listener PIP (`listenerName` for event listener objects).
* `permissions` and `options` support for template listeners.
* `wcf\data\TDatabaseObjectOptions` and `wcf\data\TDatabaseObjectPermissions` for database object-bound options and permissions validation.
* `wcf\system\cache\builder\EventListenerCacheBuilder` returns `wcf\data\event\listener\EventListener` objects instead of data arrays.
* `wcf\system\clipboard\action\UserExtendedClipboardAction` removed.
* `wcf\system\event\listener\PreParserAtUserListener` removed.
* `wcf\action\AJAXProxyAction::getData()` removed.
* Version system removed.
* `wcf\system\cache\source\RedisCacheSource` added.
* Background queue (`wcf\system\background\*`) added.
* Rewritten email system (`wcf\system\email\*`) added.
* Old email system (`wcf\system\mail\*`) deprecated.
* Abstract bulk processing system added.
* Replaced old user bulk processing with new implementation using the abstract bulk processing system.
* `conditionContainers` template event in template `noticeAdd.tpl` added.
* Use condition system for user search.
* Image proxy for images included with the image BBCode.
* Overhauled Redactor integration
	* Linebreaks mode instead of using paragraphs, works better with the PHP-side parser which works with linebreaks
	* Ported the PHP-BBCode parser, massively improves accuracy and ensures validity