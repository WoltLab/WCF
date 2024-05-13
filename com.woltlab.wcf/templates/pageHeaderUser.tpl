<nav id="topMenu" class="userPanel{if $__wcf->user->userID} userPanelLoggedIn{/if}">
	<ul class="userPanelItems">
		{if $__wcf->user->userID}
			<!-- user menu -->
			<li id="userMenu">
				<a
					class="jsTooltip"
					href="{$__wcf->user->getLink()}"
					title="{lang}wcf.user.controlPanel{/lang}"
					role="button"
					tabindex="0"
					aria-haspopup="true"
					aria-expanded="false"
				>
					{@$__wcf->getUserProfileHandler()->getAvatar()->getImageTag(32, false)} <span>{lang}wcf.user.userNote{/lang}</span>
				</a>
				<div class="userMenu userMenuControlPanel" data-origin="userMenu" tabindex="-1" hidden>
					<div class="userMenuHeader">
						<div class="userMenuTitle">{lang}wcf.user.controlPanel{/lang}</div>
					</div>
					<div class="userMenuContent">
						<div class="userMenuItem{if !MODULE_USER_RANK} userMenuItemSingleLine userMenuItemUserHeader{/if}">
							<div class="userMenuItemImage">
								{@$__wcf->getUserProfileHandler()->getUserProfile()->getAvatar()->getImageTag(48)}
							</div>
							<div class="userMenuItemContent">
								{* This is the unformatted username, custom styles might not work nicely here and
								   the consistent styling is used to provide visual anchors to identify links. *}
								<a href="{$__wcf->user->getLink()}" class="userMenuItemLink">{$__wcf->user->username}</a>
							</div>
							{if MODULE_USER_RANK}
							<div class="userMenuItemMeta">
								{if $__wcf->getUserProfileHandler()->getUserTitle()}
									<span class="badge userTitleBadge{if $__wcf->getUserProfileHandler()->getRank() && $__wcf->getUserProfileHandler()->getRank()->cssClassName} {@$__wcf->getUserProfileHandler()->getRank()->cssClassName}{/if}">{$__wcf->getUserProfileHandler()->getUserTitle()}</span>
								{/if}
								{if $__wcf->getUserProfileHandler()->getRank() && $__wcf->getUserProfileHandler()->getRank()->rankImage}
									<span class="userRankImage">{@$__wcf->getUserProfileHandler()->getRank()->getImage()}</span>
								{/if}
							</div>
							{/if}
						</div>
					</div>
					{hascontent}
						<div class="userMenuContentDivider"></div>
						<div class="userMenuContent">
							{content}
								{if $__wcf->getUserProfileHandler()->canEditOwnProfile()}
									<div class="userMenuItem userMenuItemNarrow userMenuItemSingleLine">
										<div class="userMenuItemImage">
											{icon size=16 name='pencil'}
										</div>
										<div class="userMenuItemContent">
											<a href="{link controller='User' object=$__wcf->user editOnInit=true}{/link}" class="userMenuItemLink">{lang}wcf.user.editProfile{/lang}</a>
										</div>
									</div>
								{/if}
								{if $__wcf->session->getPermission('admin.general.canUseAcp')}
									<div class="userMenuItem userMenuItemNarrow userMenuItemSingleLine">
										<div class="userMenuItemImage">
											{icon size=16 name='wrench'}
										</div>
										<div class="userMenuItemContent">
											<a href="{link isACP=true}{/link}" class="userMenuItemLink">{lang}wcf.global.acp{/lang}</a>
										</div>
									</div>
								{/if}
							{/content}
						</div>
					{/hascontent}
					<div class="userMenuContentDivider"></div>
					<div class="userMenuContent userMenuContentScrollable">
						{foreach from=$__wcf->getUserMenu()->getUserMenuItems() item=menuItem}
						<div class="userMenuItem userMenuItemNarrow userMenuItemSingleLine" data-category="{$menuItem[category]->menuItem}">
							<div class="userMenuItemImage">
								{@$menuItem[category]->getIcon()->toHtml(16)}
							</div>
							<div class="userMenuItemContent">
								<a href="{$menuItem[link]}" class="userMenuItemLink">
									{$menuItem[category]->getTitle()}
								</a>
							</div>
						</div>
						{/foreach}
					</div>
					<div class="userMenuFooter">
						<form method="post" action="{link controller='Logout'}{/link}">
							<button type="submit" class="userMenuFooterLink">{lang}wcf.user.logout{/lang}</button>
							{csrfToken}
						</form>
					</div>
				</div>
				<script data-relocate="true">
					require(["WoltLabSuite/Core/Ui/User/Menu/ControlPanel"], ({ setup }) => setup());
				</script>
			</li>
			
			<!-- user notifications -->
			{if !$__hideUserMenu|isset}
				<li id="userNotifications" data-count="{#$__wcf->getUserNotificationHandler()->getNotificationCount()}">
					<a
						class="jsTooltip"
						href="{link controller='NotificationList'}{/link}"
						title="{lang}wcf.user.notification.notifications{/lang}"
						role="button"
						tabindex="0"
						aria-haspopup="true"
						aria-expanded="false"
					>
						{icon size=32 name='bell' type='solid'} <span>{lang}wcf.user.notification.notifications{/lang}</span>{if $__wcf->getUserNotificationHandler()->getNotificationCount()} <span class="badge badgeUpdate">{#$__wcf->getUserNotificationHandler()->getNotificationCount()}</span>{/if}
					</a>
					{if !OFFLINE || $__wcf->session->getPermission('admin.general.canViewPageDuringOfflineMode')}
						<script data-relocate="true">
							require(["WoltLabSuite/Core/Language", "WoltLabSuite/Core/Ui/User/Menu/Data/Notification"], (Language, { setup }) => {
								Language.addObject({
									"wcf.user.notification.enableDesktopNotifications": "{jslang}wcf.user.notification.enableDesktopNotifications{/jslang}",
									"wcf.user.notification.enableDesktopNotifications.button": "{jslang}wcf.user.notification.enableDesktopNotifications.button{/jslang}",
								});

								setup({
									noItems: '{jslang}wcf.user.notification.noMoreNotifications{/jslang}',
									settingsLink: '{link controller='NotificationSettings' encode=false}{/link}',
									settingsTitle: '{jslang}wcf.user.notification.settings{/jslang}',
									showAllLink: '{link controller='NotificationList' encode=false}{/link}',
									showAllTitle: '{jslang}wcf.user.notification.showAll{/jslang}',
									title: '{jslang}wcf.user.notification.notifications{/jslang}',
								});
							});
						</script>
					{/if}
				</li>
			{/if}
		{else}
			{if $__wcf->getLanguage()->getLanguages()|count > 1}
				<li id="pageLanguageContainer">
					<script data-relocate="true">
						require(['WoltLabSuite/Core/Language/Chooser'], function(LanguageChooser) {
							var languages = {
								{implode from=$__wcf->getLanguage()->getLanguages() item=_language}
									'{@$_language->languageID}': {
										iconPath: '{@$_language->getIconPath()|encodeJS}',
										languageName: '{@$_language|encodeJS}',
										languageCode: '{@$_language->languageCode|encodeJS}'
									}
								{/implode}
							};
								
							var callback = function(listItem) {
								var location;
								var languageCode = elData(listItem, 'language-code');
								var link = elBySel('link[hreflang="' + languageCode + '"]');
								if (link !== null) {
									location = link.href;
								}
								else {
									location = window.location.toString().replace(/#.*/, '').replace(/(\?|&)l=[0-9]+/g, '');
								}
								
								var delimiter = (location.indexOf('?') == -1) ? '?' : '&';
								window.location = location + delimiter + 'l=' + elData(listItem, 'language-id') + window.location.hash;
							};
							
							LanguageChooser.init('pageLanguageContainer', 'pageLanguageID', {@$__wcf->getLanguage()->languageID}, languages, callback);
						});
					</script>
				</li>
			{/if}
			<li id="userLogin">
				<a
					class="loginLink"
					href="{link controller='Login' url=$__wcf->getRequestURI()}{/link}"
					rel="nofollow"
				>{lang}wcf.user.button.login{/lang}</a>
			</li>
			{if $__userAuthConfig->canRegister}
				<li id="userRegistration">
					<a
						class="registrationLink"
						href="{link controller='Register'}{/link}"
						rel="nofollow"
					>{lang}wcf.user.button.register{/lang}</a>
				</li>
			{/if}
		{/if}
		
		{if !$__hideUserMenu|isset}
			{if $__wcf->user->userID && $__wcf->session->getPermission('mod.general.canUseModeration')}
				<li id="outstandingModeration" data-count="{#$__wcf->getModerationQueueManager()->getUnreadModerationCount()}">
					<a
						class="jsTooltip"
						href="{link controller='ModerationList'}{/link}"
						title="{lang}wcf.moderation.moderation{/lang}"
						role="button"
						tabindex="0"
						aria-haspopup="true"
						aria-expanded="false"
					>
						{icon size=32 name='triangle-exclamation'}
						<span>{lang}wcf.moderation.moderation{/lang}</span>
						{if $__wcf->getModerationQueueManager()->getUnreadModerationCount()}<span class="badge badgeUpdate">{#$__wcf->getModerationQueueManager()->getUnreadModerationCount()}</span>{/if}
					</a>
					{if !OFFLINE || $__wcf->session->getPermission('admin.general.canViewPageDuringOfflineMode')}
						<script data-relocate="true">
							require(["WoltLabSuite/Core/Ui/User/Menu/Data/ModerationQueue"], ({ setup }) => {
								setup({
									deletedContent: '{jslang}wcf.moderation.showDeletedContent{/jslang}',
									deletedContentLink: '{link controller='DeletedContentList' encode=false}{/link}',
									noItems: '{jslang}wcf.moderation.noMoreItems{/jslang}',
									showAllLink: '{link controller='ModerationList' encode=false}{/link}',
									showAllTitle: '{jslang}wcf.moderation.showAll{/jslang}',
									title: '{jslang}wcf.moderation.moderation{/jslang}'
								});
							});
						</script>
					{/if}
				</li>
			{/if}
			
			{event name='menuItems'}
		{/if}
		
		<!-- page search -->
		<li>
			<a href="{link controller='Search'}{/link}" id="userPanelSearchButton" class="jsTooltip" title="{lang}wcf.global.search{/lang}">{icon size=32 name='magnifying-glass'} <span>{lang}wcf.global.search{/lang}</span></a>
		</li>
	</ul>
</nav>
{if $__wcf->user->userID}
	<button type="button" class="pageHeaderUserMobile" aria-expanded="false" aria-label="{lang}wcf.menu.user{/lang}">
		<span class="pageHeaderUserMobileInactive">
			{@$__wcf->getUserProfileHandler()->getAvatar()->getImageTag(32, false)}
		</span>
		<span class="pageHeaderUserMobileActive">
			{icon size=32 name='xmark'}
		</span>
	</button>
{else}
	<a
		href="{link controller='Login' url=$__wcf->getRequestURI()}{/link}"
		class="userPanelLoginLink jsTooltip"
		title="{lang}wcf.user.button.login{/lang}"
		rel="nofollow"
	>
		{icon size=32 name='arrow-right-to-bracket'}
	</a>
{/if}
