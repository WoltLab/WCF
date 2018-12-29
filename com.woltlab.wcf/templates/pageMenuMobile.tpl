{* main menu / page options / breadcrumbs *}
<div id="pageMainMenuMobile" class="pageMainMenuMobile menuOverlayMobile" data-page-logo="{$__wcf->getStyleHandler()->getStyle()->getPageLogo()}">
	<ol class="menuOverlayItemList" data-title="{lang}wcf.menu.page{/lang}">
		<li class="menuOverlayTitle">{lang}wcf.menu.page.navigation{/lang}</li>
		{foreach from=$__wcf->getBoxHandler()->getBoxByIdentifier('com.woltlab.wcf.MainMenu')->getMenu()->getMenuItemNodeList() item=menuItemNode}
			<li class="menuOverlayItem">
				{assign var=__outstandingItems value=$menuItemNode->getOutstandingItems()}
				<a href="{$menuItemNode->getURL()}" class="menuOverlayItemLink{if $__outstandingItems} menuOverlayItemBadge{/if}{if $menuItemNode->isActiveNode()} active{/if}"{if $menuItemNode->isExternalLink() && EXTERNAL_LINK_TARGET_BLANK} target="_blank"{/if}>
					<span class="menuOverlayItemTitle">{lang}{$menuItemNode->title}{/lang}</span>
					{if $__outstandingItems}
						<span class="badge badgeUpdate">{#$__outstandingItems}</span>
					{/if}
				</a>
				
				{if $menuItemNode->hasChildren()}<ol class="menuOverlayItemList">{else}</li>{/if}
					
					{if !$menuItemNode->hasChildren() && $menuItemNode->isLastSibling()}
						{@"</ol></li>"|str_repeat:$menuItemNode->getOpenParentNodes()}
					{/if}
		{/foreach}
		<li class="menuOverlayItemSpacer"></li>
		<li class="menuOverlayItem" data-more="com.woltlab.wcf.search">
			<a href="#" class="menuOverlayItemLink box24">
				<span class="icon icon24 fa-search"></span>
				<span class="menuOverlayItemTitle">{lang}wcf.global.search{/lang}</span>
			</a>
		</li>
		<li class="menuOverlayItem" id="pageMainMenuMobilePageOptionsContainer">
			<a href="#" class="menuOverlayItemLink box24">
				<span class="icon icon24 fa-gears"></span>
				<span class="menuOverlayItemTitle">{lang}wcf.menu.page.options{/lang}</span>
			</a>
			<ol class="menuOverlayItemList">
				<li class="menuOverlayItem jsMenuOverlayItemPlaceholder"><a href="#">(placeholder)</a></li>
			</ol>
		</li>
		
		{event name='menuItems'}
		
		{hascontent}
			<li class="menuOverlayTitle">{lang}wcf.menu.page.location{/lang}</li>
			{content}
				{assign var=__breadcrumbsDepth value=0}
				{foreach from=$__wcf->getBreadcrumbs() item=$breadcrumb}
					<li class="menuOverlayItem">
						<a href="{$breadcrumb->getURL()}" class="menuOverlayItemLink">
							<span{if $__breadcrumbsDepth} style="padding-left: {$__breadcrumbsDepth * 20}px" {/if} class="box24">
								<span class="icon icon24 fa-{if $__breadcrumbsDepth}caret-right{else}home{/if}"></span>
								<span class="menuOverlayItemTitle">{$breadcrumb->getLabel()}</span>
							</span>
						</a>
					</li>
					{assign var=__breadcrumbsDepth value=$__breadcrumbsDepth + 1}
				{/foreach}
			{/content}
		{/hascontent}
	</ol>
</div>

{* user menu *}
<div id="pageUserMenuMobile" class="pageUserMenuMobile menuOverlayMobile" data-page-logo="{$__wcf->getStyleHandler()->getStyle()->getPageLogo()}">
	<ol class="menuOverlayItemList" data-title="{lang}wcf.menu.user{/lang}">
		{if $__wcf->user->userID}
			{* logged-in *}
			<li class="menuOverlayTitle">{lang}wcf.menu.user{/lang}</li>
			<li class="menuOverlayItem">
				<a href="{link controller='User' object=$__wcf->user}{/link}" class="menuOverlayItemLink box24">
					{@$__wcf->getUserProfileHandler()->getAvatar()->getImageTag(24)}
					<span class="menuOverlayItemTitle">{$__wcf->user->username}</span>
				</a>
			</li>
			<li class="menuOverlayItem">
				<a href="{link controller='Settings'}{/link}" class="menuOverlayItemLink box24">
					<span class="icon icon24 fa-cog"></span>
					<span class="menuOverlayItemTitle">{lang}wcf.user.panel.settings{/lang}</span>
				</a>
				<ol class="menuOverlayItemList">
					{foreach from=$__wcf->getUserMenu()->getMenuItems('') item=menuCategory}
						<li class="menuOverlayTitle">{lang}{$menuCategory->menuItem}{/lang}</li>
						{foreach from=$__wcf->getUserMenu()->getMenuItems($menuCategory->menuItem) item=menuItem}
							<li class="menuOverlayItem">
								<a href="{$menuItem->getProcessor()->getLink()}" class="menuOverlayItemLink">{@$menuItem}</a>
							</li>
						{/foreach}
					{/foreach}
				</ol>
			</li>
			{if $__wcf->session->getPermission('admin.general.canUseAcp')}
				<li class="menuOverlayItem">
					<a href="{link isACP=true}{/link}" class="menuOverlayItemLink box24">
						<span class="icon icon24 fa-wrench"></span>
						<span class="menuOverlayItemTitle">{lang}wcf.global.acp.short{/lang}</span>
					</a>
				</li>
			{/if}
			<li class="menuOverlayItemSpacer"></li>
			<li class="menuOverlayItem" data-more="com.woltlab.wcf.notifications">
				<a href="{link controller='NotificationList'}{/link}" class="menuOverlayItemLink menuOverlayItemBadge box24" data-badge-identifier="userNotifications">
					<span class="icon icon24 fa-bell-o"></span>
					<span class="menuOverlayItemTitle">{lang}wcf.user.notification.notifications{/lang}</span>
					{if $__wcf->getUserNotificationHandler()->getNotificationCount()}<span class="badge badgeUpdate">{#$__wcf->getUserNotificationHandler()->getNotificationCount()}</span>{/if}
				</a>
			</li>
			{if $__wcf->user->userID && $__wcf->session->getPermission('mod.general.canUseModeration')}
				<li class="menuOverlayItem" data-more="com.woltlab.wcf.moderation">
					<a href="#" class="menuOverlayItemLink menuOverlayItemBadge box24" data-badge-identifier="outstandingModeration">
						<span class="icon icon24 fa-exclamation-triangle"></span>
						<span class="menuOverlayItemTitle">{lang}wcf.moderation.moderation{/lang}</span>
						{if $__wcf->getModerationQueueManager()->getUnreadModerationCount()}<span class="badge badgeUpdate">{#$__wcf->getModerationQueueManager()->getUnreadModerationCount()}</span>{/if}
					</a>
				</li>
			{/if}
			
			{event name='userMenuItems'}
			
			<li class="menuOverlayItemSpacer"></li>
			<li class="menuOverlayItem">
				<a href="{link controller='Logout'}t={@SECURITY_TOKEN}{/link}" class="menuOverlayItemLink box24">
					<span class="icon icon24 fa-sign-out"></span>
					<span class="menuOverlayItemTitle">{lang}wcf.user.logout{/lang}</span>
				</a>
			</li>
		{else}
			{* guest *}
			<li class="menuOverlayTitle">{lang}wcf.menu.user{/lang}</li>
			{if !$__disableLoginLink|isset}
				<li class="menuOverlayItem" data-more="com.woltlab.wcf.login">
					<a href="#" class="menuOverlayItemLink box24">
						<span class="icon icon24 fa-sign-in"></span>
						<span class="menuOverlayItemTitle">{lang}wcf.user.login{/lang}</span>
					</a>
				</li>
			{/if}
			{if !REGISTER_DISABLED}
				<li class="menuOverlayItem">
					<a href="{link controller='Register'}{/link}" class="menuOverlayItemLink box24">
						<span class="icon icon24 fa-user-plus"></span>
						<span class="menuOverlayItemTitle">{lang}wcf.user.register{/lang}</span>
					</a>
				</li>
			{/if}
			
			{event name='guestUserMenuItems'}
			
			{if $__wcf->getLanguage()->getLanguages()|count > 1}
				<li class="menuOverlayItemSpacer"></li>
				<li class="menuOverlayTitle">{lang}wcf.user.language{/lang}</li>
				<li class="menuOverlayItem">
					<a href="#" class="menuOverlayItemLink box24">
						<img src="{$__wcf->getLanguage()->getIconPath()}" alt="">
						<span class="menuOverlayItemTitle">{$__wcf->getLanguage()}</span>
					</a>
					<ol class="menuOverlayItemList" data-title="{lang}wcf.user.language{/lang}">
						{foreach from=$__wcf->getLanguage()->getLanguages() item=_language}
							<li class="menuOverlayItem" data-more="com.woltlab.wcf.language" data-language-code="{$_language->getFixedLanguageCode()}" data-language-id="{@$_language->languageID}">
								<a href="#" class="menuOverlayItemLink box24">
									<img src="{$_language->getIconPath()}" alt="">
									<span class="menuOverlayItemTitle">{$_language}</span>
								</a>
							</li>
						{/foreach}
					</ol>
				</li>
			{/if}
		{/if}
	</ol>
</div>
