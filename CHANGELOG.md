# Changelog

## 2.2 (Vortex)

### 2.2.0 Alpha 1 (XXXX-YY-ZZ)

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
* `wcf\action\AJAXProxyAction::getData()` remove.
