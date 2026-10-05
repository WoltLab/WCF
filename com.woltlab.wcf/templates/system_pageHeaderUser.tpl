<nav id="topMenu" class="userPanel{if !$__wcf->user->isGuest()} userPanelLoggedIn{/if}">
	<ul class="userPanelItems">
		<!-- page search -->
		<li>
			<a href="{link controller='Search'}{/link}" id="userPanelSearchButton" class="jsTooltip" title="{lang}wcf.global.search{/lang}">{icon size=32 name='magnifying-glass'} <span>{lang}wcf.global.search{/lang}</span></a>
		</li>
		
		{if !$__hideUserMenu|isset}
			{event name='menuItems'}
			
			{if !$__wcf->user->isGuest() && $__wcf->session->getPermission('mod.general.canUseModeration')}
				<li id="outstandingModeration" data-count="{$__wcf->getModerationQueueManager()->getUnreadModerationCount()}">
					<a
						class="jsTooltip"
						href="{link controller='ModerationList'}filters[status]=0{/link}"
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
									showAllLink: '{link controller='ModerationList' encode=false}filters[status]=0{/link}',
									showAllTitle: '{jslang}wcf.moderation.showAll{/jslang}',
									title: '{jslang}wcf.moderation.moderation{/jslang}'
								});
							});
						</script>
					{/if}
				</li>
			{/if}
		{/if}
		
		{if !$__wcf->user->isGuest()}
			{if !$__hideUserMenu|isset}
				<li id="userNotifications" data-count="{$__wcf->getUserNotificationHandler()->getNotificationCount()}">
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
							require(["WoltLabSuite/Core/Ui/User/Menu/Data/Notification"], ({ setup }) => {
								{jsphrase name='wcf.user.notification.enableDesktopNotifications'}
								{jsphrase name='wcf.user.notification.enableDesktopNotifications.button'}

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
					{unsafe:$__wcf->getUserProfileHandler()->getAvatar()->getImageTag(32, false)} <span>{lang}wcf.user.userNote{/lang}</span>
				</a>
				<div class="userMenu userMenuControlPanel" data-origin="userMenu" tabindex="-1" hidden>
					<div class="userMenuHeader">
						<div class="userMenuTitle">{lang}wcf.user.controlPanel{/lang}</div>
					</div>
					<div class="userMenuContent">
						<div class="userMenuItem{if !MODULE_USER_RANK} userMenuItemSingleLine userMenuItemUserHeader{/if}">
							<div class="userMenuItemImage">
								{unsafe:$__wcf->getUserProfileHandler()->getUserProfile()->getAvatar()->getImageTag(48)}
							</div>
							<div class="userMenuItemContent">
								{* This is the unformatted username, custom styles might not work nicely here and
								   the consistent styling is used to provide visual anchors to identify links. *}
								<a href="{$__wcf->user->getLink()}" class="userMenuItemLink">{$__wcf->user->username}</a>
							</div>
							{if MODULE_USER_RANK}
							<div class="userMenuItemMeta">
								{if $__wcf->getUserProfileHandler()->getUserTitle()}
									<span class="badge userTitleBadge{if $__wcf->getUserProfileHandler()->getRank() && $__wcf->getUserProfileHandler()->getRank()->cssClassName} {unsafe:$__wcf->getUserProfileHandler()->getRank()->cssClassName}{/if}">{$__wcf->getUserProfileHandler()->getUserTitle()}</span>
								{/if}
								{if $__wcf->getUserProfileHandler()->getRank() && $__wcf->getUserProfileHandler()->getRank()->rankImage}
									<span class="userRankImage">{unsafe:$__wcf->getUserProfileHandler()->getRank()->getImage()}</span>
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
											{icon size=24 name='pencil'}
										</div>
										<div class="userMenuItemContent">
											<a href="{link controller='User' object=$__wcf->user editOnInit=true}{/link}" class="userMenuItemLink">{lang}wcf.user.editProfile{/lang}</a>
										</div>
									</div>
								{/if}
								{if $__wcf->session->getPermission('admin.general.canUseAcp')}
									<div class="userMenuItem userMenuItemNarrow userMenuItemSingleLine">
										<div class="userMenuItemImage">
											{icon size=24 name='wrench'}
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
								{unsafe:$menuItem[category]->getIcon()->toHtml(24)}
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
		{else}
			{if $__wcf->getLanguage()->getLanguages()|count > 1}
				<li id="pageLanguageContainer" class="dropdown">
					<a
						href="#"
						class="dropdownToggle jsTooltip"
						title="{lang}wcf.user.language{/lang}"
						role="button"
						aria-label="{lang}wcf.user.language{/lang}"
					>
						{icon size=32 name='language'} <span>{lang}wcf.user.language{/lang}</span>
					</a>
					<ul class="dropdownMenu">
						{foreach from=$__wcf->getLanguage()->getLanguages() item=_language}
							<li>
								<a
									href="#"
									data-switch-language="{$_language->languageID}"
									data-language-code="{$_language->languageCode}"
									lang="{$_language->languageCode}"
									{if $_language->languageID === $__wcf->getLanguage()->languageID} aria-current="true"{/if}
								>
									<img src="{$_language->getIconPath()}" alt="" class="iconFlag">
									<span>{$_language}</span>
								</a>
							</li>
						{/foreach}
					</ul>
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
	</ul>
</nav>
{if !$__wcf->user->isGuest()}
	<button
		type="button"
		class="pageHeaderUserMobile"
		aria-controls="userMenuDrawer"
		aria-expanded="false"
		aria-label="{lang}wcf.menu.user{/lang}"
		data-drawer-target="userMenuDrawer"
	>
		{unsafe:$__wcf->getUserProfileHandler()->getAvatar()->getImageTag(32, false)}
	</button>
	
	{* The tabs and their panels are built on the client from the registered user menus. *}
	<div id="userMenuDrawer" class="userMenuDrawer" aria-label="{lang}wcf.menu.user{/lang}">
		<div class="userMenuDrawerHead">
			{unsafe:$__wcf->getUserProfileHandler()->getAvatar()->getImageTag(48, false)}
			<div class="userMenuDrawerUser">
				<span class="userMenuDrawerUsername">{$__wcf->user->username}</span>
				{if MODULE_USER_RANK && $__wcf->getUserProfileHandler()->getUserTitle()}
					<span class="userMenuDrawerUserTitle">{$__wcf->getUserProfileHandler()->getUserTitle()}</span>
				{/if}
			</div>
			<button type="button" class="userMenuDrawerClose" data-drawer-close aria-label="{lang}wcf.global.button.close{/lang}">
				{icon size=24 name='xmark'}
			</button>
		</div>
		<div class="userMenuDrawerTabs" role="tablist" aria-label="{lang}wcf.menu.user{/lang}"></div>
		<div class="userMenuDrawerPanel" role="tabpanel" tabindex="0"></div>
	</div>
{/if}
