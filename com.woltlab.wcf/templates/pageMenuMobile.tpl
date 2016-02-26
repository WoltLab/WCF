{* main menu / page options / breadcrumbs *}
<div id="pageMainMenuMobile" class="pageMainMenuMobile menuOverlayMobile" data-page-logo="{$__wcf->getPath()}images/default-logo.png">
	<ol class="menuOverlayItemList" data-title="TODO: menu">
		<li class="menuOverlayTitle">TODO: menu</li>
		<li class="menuOverlayItem">
			<a href="#" class="menuOverlayItemLink box24">
				<span class="icon icon24 fa-sitemap"></span>
				<span class="menuOverlayItemTitle">TODO: navigation</span>
			</a>
			<ol class="menuOverlayItemList">
				{foreach from=$__wcf->getBoxHandler()->getBoxes('mainMenu')[0]->getMenu()->getMenuItemNodeList() item=menuItemNode}
				<li class="menuOverlayItem">
					{assign var=__outstandingItems value=$menuItemNode->getMenuItem()->getOutstandingItems()}
					<a href="{$menuItemNode->getMenuItem()->getURL()}" class="menuOverlayItemLink{if $__outstandingItems} menuOverlayItemBadge{/if}">
						<span class="menuOverlayItemTitle">{lang}{$menuItemNode->getMenuItem()->title}{/lang}</span>
						{if $__outstandingItems}
							<span class="badge badgeInverse">{#$__outstandingItems}</span>
						{/if}
					</a>
					
					{if $menuItemNode->hasChildren()}<ol class="menuOverlayItemList">{else}</li>{/if}
						
						{if !$menuItemNode->hasChildren() && $menuItemNode->isLastSibling()}
							{@"</ol></li>"|str_repeat:$menuItemNode->getOpenParentNodes()}
						{/if}
						{/foreach}
					</ol>
				</li>
				{hascontent}
					<li class="menuOverlayItem">
						<a href="#" class="menuOverlayItemLink box24">
							<span class="icon icon24 fa-gears"></span>
							<span class="menuOverlayItemTitle">TODO: page options</span>
						</a>
						<ol class="menuOverlayItemList">
							{content}
								{if !$__pageOptions|empty}
									{@$__pageOptions}
								{/if}
								
								{event name='pageOptions'}
							{/content}
						</ol>
					</li>
				{/hascontent}
				{hascontent}
					<li class="menuOverlayTitle">TODO: current location</li>
					<li class="menuOverlayItem">
						<a href="#" class="menuOverlayItemLink box24">
							<span class="icon icon24 fa-cogs"></span>
							<span class="menuOverlayItemTitle">TODO: current location</span>
						</a>
						<ol class="menuOverlayItemList">
							{content}
							{assign var=__breadcrumbsDepth value=0}
							{foreach from=$__wcf->getBreadcrumbs() item=$breadcrumb}
								<li class="menuOverlayItem">
									<a href="{$breadcrumb->getURL()}" class="menuOverlayItemLink">
										<span class="menuOverlayItemTitle"{if $__breadcrumbsDepth} style="padding-left: {$__breadcrumbsDepth * 10}px" {/if}>
											<span class="icon icon24 fa-{if $__breadcrumbsDepth}caret-right{else}home{/if}"></span>
											{$breadcrumb->getLabel()}
										</span>
									</a>
								</li>
								{assign var=__breadcrumbsDepth value=$__breadcrumbsDepth + 1}
							{/foreach}
							{/content}
						</ol>
					</li>
				{/hascontent}
			</ol>
		</li>
	</ol>
</div>

{* user menu *}
{if $__wcf->user->userID}
	<div id="pageUserMenuMobile" class="pageUserMenuMobile menuOverlayMobile" data-page-logo="{$__wcf->getPath()}images/default-logo.png">
		<ol class="menuOverlayItemList" data-title="TODO: user menu">
			<li class="menuOverlayTitle">{lang}wcf.user.controlPanel{/lang}</li>
			<li class="menuOverlayItem">
				<a href="{link controller='User' object=$__wcf->user}{/link}" class="menuOverlayItemLink box24">
					{@$__wcf->getUserProfileHandler()->getAvatar()->getImageTag(24)}
					<span class="menuOverlayItemTitle">{$__wcf->user->username}</span>
				</a>
			</li>
			<li class="menuOverlayItem">
				<a href="{link controller='Settings'}{/link}" class="menuOverlayItemLink box24">
					<span class="icon icon24 fa-cog"></span>
					<span class="menuOverlayItemTitle">Einstellungen</span>
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
				<a href="{link controller='NotificationList'}{/link}" class="menuOverlayItemLink box24">
					<span class="icon icon24 fa-bell-o"></span>
					<span class="menuOverlayItemTitle">{lang}wcf.user.notification.notifications{/lang}</span>
				</a>
			</li>
			<li class="menuOverlayItem">
				<a href="#" class="menuOverlayItemLink box24">
					<span class="icon icon24 fa-exclamation-triangle"></span>
					<span class="menuOverlayItemTitle">{lang}wcf.moderation.moderation{/lang}</span>
				</a>
			</li>
			
			{event name='userMenuItems'}
			
			<li class="menuOverlayItemSpacer"></li>
			<li class="menuOverlayItem">
				<a href="{link controller='Logout'}t={@SECURITY_TOKEN}{/link}" class="menuOverlayItemLink box24">
					<span class="icon icon24 fa-sign-out"></span>
					<span class="menuOverlayItemTitle">{lang}wcf.user.logout{/lang}</span>
				</a>
			</li>
		</ol>
	</div>
{/if}
