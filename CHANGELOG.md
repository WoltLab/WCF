# Changelog

## 3.0 (Vortex)

### 3.0.0 Alpha 1 (XXXX-YY-ZZ)

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
* Core object `wcf\system\search\SearchEngine` added.
* Added 'most online users' to statistics box.
* Added icon size 64
* Enhanced user ignore feature
* Removed delayed redirect from login/logout
* `appendSession` in LinkHandler is now deprecated
* Added cover photo in user profiles
* Using cookies for ACP sessions
* WCF is now a standalone app
* Overhauled redactor integration
* Overhauled bbcode/html handling
* Removed option import/export
* Overhauled style editor
* Added replacements for WCF.Like, WCF.User.List and $.ui.wcfPages
* Added update support for styles
* `\wcf\system\condition\MultiPageControllerCondition` has been replaced by `wcf\system\condition\page\MultiPageCondition`
* Added special CSS class to cookie policy notice (`cookiePolicyNotice`)
* Improved confirmation messages (`<span class="confirmationObject">...</span>`)
* Added users online list pagination
* Added support for embedded youtube playlists
* Scaled embedded youtube videos to maximum width
* `\wcf\form\AbstractCaptchaForm`: added parameter to force captcha usage for registered users.
* Added global disable switch for languages.
* Overhauled page tracking in sessions / user online locations
* Overhauled language import form
* Removed sitemap function/overlay
* Added rebuild polls worker
* Added notification feed page

#### CMS

* Introduced new page, menu, box and media management system.
* Replaced object type definition `com.woltlab.wcf.page` with new CMS pages.
* Replaced header/footer menu with new CMS menus.
* Replaced dashboard box system with new CMS box system.
* User online location is handled via the `wcf\data\page\Page` objects. Complex locations can use the online location-related methods of `wcf\system\page\handler\IMenuPageHandler`.
* Added page-relevant data-attributes on body tag (`data-page-id`, `data-page-identifier`).

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

#### Template / Design Overhaul

* Overhauled header/footer templates
* Overhauled message templates/styling
* Overhauled search area in page header
* Overhauled user profile header
* Overhauled media queries
* Overhauled user login
* Overhauled exception view
* Redesigned ACP login
* Redesigned list of attached images in messages

* Introduced sticky page header
* Replaced LESS with SCSS
* Removed collapsible sidebar
* Removed obsolete CSS classes `framed`, `containerPadding`, `dividers`, `badgeInverse`
* Replaced `.infoBoxList` boxes with `.footerBoxes`
* Replaced `<fieldset>` tags with `<section class="section">`
* Replaced `<legend>` tags with `<h2 class="sectionTitle">`
* Replaced `.container`/`.marginTop` with `.section`
* Replaced `.boxHeadline` with `.contentHeader`
* Replaced `.boxSubHeadline` with `.sectionTitle`
* Replaced `.sidebarNavigation` with `.boxMenu`
* Replaced deprecated icon class (`icon-*` => `fa-*`)
* Moved closing head / body tags into `footer` template
* Moved documentHeader, head, body, contentHeader, userNotice into `header` template
* Replaced `$sidebarOrientation` with `$sidebarLeft`/`$sidebarRight`
* Renamed `{event name='*fieldsets'}` to `{event name='*sections'}`
* Introduced `.separatorLeft`/`.separatorRight`
* Removed `.tabularBoxTitle` if table title and page tite are identical
* Moved the "No-JS"-Warning to footer template.
* Tables can now be horizontally scrolled on mobile
* Added mobile support for ACP
* Added basic grid layout classes
* Renamed `.pageNavigation` to `.pagination`
* Renamed `.navigation`/`.navigationIcons` to `.pageNavigation`/`.pageNavigationIcons`
* Added CSS classes to style font sizes in tag cloud (`tagWeight1` - `tagWeight7`)

#### Deprecated Code

* Object type definition `com.woltlab.wcf.user.online.location` deprecated.
* Object type definition `com.woltlab.wcf.page` deprecated.

#### Removed Code

* `wcf\system\clipboard\action\UserExtendedClipboardAction` removed.
* `wcf\system\event\listener\PreParserAtUserListener` removed.
* `wcf\action\AJAXProxyAction::getData()` removed.
* `wcf\system\page\PageManager` removed.
* `wcf\system\option\PageMultiSelectOptionType` removed.
* `wcf\system\option\PageSelectOptionType` removed.
* `wcf\system\user\online\location\UserLocation` removed.
* Version system removed.
* Support for query string based sessions in Frontend removed.
* Language server system removed.
* Deprecated methods in `wcf\util\StringUtil` removed.
* Option `message_sidebar_enable_message_group_starter_icon` removed.
* Option `module_dashboard_page` removed.
* Option `module_privacy_policy_page` removed.
* Option `show_clock` removed.
* Option `message_sidebar_enable_rank` removed.
* Option `message_sidebar_enable_avatar` removed.
* Removed obsolete `$activeMenuItem` in frontend forms/pages
* Obsolete interface `wcf\page\ITrackablePage` deprecated.
* PIP `wcf\system\package\plugin\SitemapPackageInstallationPlugin` removed.
* Option `share_buttons_show_count` removed.

#### Documentation

* Added missing and fixed existing PHPDoc comments.
* `@property-read` tags for database table columns of classes extending `wcf\data\DatabaseObject`.
* `@method` tags for classes extending `wcf\data\AbstractDatabaseObjectAction` to specify return types.
* `@mixin` tag for classes extending `wcf\data\DatabaseObjectDecorator` for autocompletion/recognition of properties and methods of the decorated object.
* `@method` tag for classes extending `wcf\data\DatabaseObjectEditor` to specify return type.
* `@method` and `@property` tags for classes extending `wcf\data\DatabaseObjectList` to specify (return) types.
* `@property` tag for classes extending `wcf\page\MultipleLinkPage` to specify type.
* `@mixin` tag for classes extending `wcf\system\database\statement\PreparedStatement` for autocompletion/recognition of properties and methods of the decorated `\PDOStatement` object.
* `@method` tags for `wcf\system\io\File` and `wcf\system\io\GZipFile` for autocompletion/recognition of methods called via `__call()`.
