# Changelog

## 2.2 (Vortex)

### 2.2.0 Alpha 1 (XXXX-YY-ZZ)

* instruction file name for most PIPs has default value now.
* `options` support for cronjobs.
* `name` attribute for cronjob PIP (`cronjobName` for cronjob objects).
* `permissions` and `options` support for event listeners.
* `name` attribute for event listener PIP (`listenerName` for event listener objects).
* `permissions` and `options` support for template listeners.
* `wcf\data\TDatabaseObjectOptions` and `wcf\data\TDatabaseObjectPermissions` for database object-bound options and permissions validation.
* `wcf\system\cache\builder\EventListenerCacheBuilder` returns `wcf\data\event\listener\EventListener` objects instead of data arrays.
* `wcf\system\clipboard\action\UserExtendedClipboardAction` removed.
* `wcf\system\event\listener\PreParserAtUserListener` removed.

