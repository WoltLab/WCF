<nav id="topMenu" class="userPanel{if $__wcf->user->userID} userPanelLoggedIn{/if}">
	{if $__wcf->user->userID}
		<span class="userPanelAvatar" aria-hidden="true">{@$__wcf->getUserProfileHandler()->getAvatar()->getImageTag(32)}</span>
	{else}
		<a href="{link controller='Login' url=$__wcf->getRequestURI()}{/link}" class="userPanelLoginLink jsTooltip" title="{lang}wcf.user.loginOrRegister{/lang}">
			<span class="icon icon32 fa-sign-in" aria-hidden="true"></span>
		</a>
	{/if}
	
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
					{@$__wcf->getUserProfileHandler()->getAvatar()->getImageTag(32)} <span>{lang}wcf.user.userNote{/lang}</span>
				</a>
				<div class="userMenu userMenuControlPanel" data-origin="userMenu" tabindex="-1" hidden>
					<div class="userMenuHeader">
						<div class="userMenuTitle">{lang}wcf.user.controlPanel{/lang}</div>
					</div>
					<div class="userMenuContent">
						<div class="userMenuItem{if !MODULE_USER_RANK} userMenuItemSingleLine{/if}">
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
					<div class="userMenuContentDivider"></div>
					<div class="userMenuContent">
						<div class="userMenuItem userMenuItemNarrow userMenuItemSingleLine">
							<div class="userMenuItemImage">
								<span class="icon icon32 fa-user-circle-o"></span>
							</div>
							<div class="userMenuItemContent">
								<a href="{$__wcf->user->getLink()}" class="userMenuItemLink">{lang}wcf.user.myProfile{/lang}</a>
							</div>
						</div>
						{if $__wcf->session->getPermission('admin.general.canUseAcp')}
						<div class="userMenuItem userMenuItemNarrow userMenuItemSingleLine">
							<div class="userMenuItemImage">
								<span class="icon icon32 fa-wrench"></span>
							</div>
							<div class="userMenuItemContent">
								<a href="{link isACP=true}{/link}" class="userMenuItemLink">{lang}wcf.global.acp{/lang}</a>
							</div>
						</div>
						{/if}
					</div>
					<div class="userMenuContentDivider"></div>
					<div class="userMenuContent userMenuContentScrollable">
						{foreach from=$__wcf->getUserMenu()->getUserMenuItems() item=menuItem}
						<div class="userMenuItem userMenuItemNarrow userMenuItemSingleLine" data-category="{$menuItem[category]->menuItem}">
							<div class="userMenuItemImage">
								<span class="icon icon32 {$menuItem[category]->getIconClassName()}"></span>
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
							<a href="#" class="userMenuFooterLink" role="button">{lang}wcf.user.logout{/lang}</a>
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
						<span class="icon icon32 fa-bell-o"></span> <span>{lang}wcf.user.notification.notifications{/lang}</span>{if $__wcf->getUserNotificationHandler()->getNotificationCount()} <span class="badge badgeUpdate">{#$__wcf->getUserNotificationHandler()->getNotificationCount()}</span>{/if}
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
				<a class="loginLink" href="{link controller='Login' url=$__wcf->getRequestURI()}{/link}">{lang}wcf.user.loginOrRegister{/lang}</a>
			</a>
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
						<span class="icon icon32 fa-exclamation-triangle"></span>
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
			<a href="{link controller='Search'}{/link}" id="userPanelSearchButton" class="jsTooltip" title="{lang}wcf.global.search{/lang}"><span class="icon icon32 fa-search"></span> <span>{lang}wcf.global.search{/lang}</span></a>
		</li>
	</ul>
</nav>
