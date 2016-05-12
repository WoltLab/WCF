{* main menu *}
<div id="pageMainMenuMobile" class="pageMainMenuMobile menuOverlayMobile" data-page-logo="{$__wcf->getPath()}images/default-logo.png">
	<ol class="menuOverlayItemList" data-title="{lang}wcf.menu.page{/lang}">
		<li class="menuOverlayTitle">{lang}wcf.menu.page{/lang}</li>
		{foreach from=$__wcf->getACPMenu()->getMenuItems('') item=_sectionMenuItem}
			<li class="menuOverlayItem">
				<a href="#" class="menuOverlayItemLink box24{if $_sectionMenuItem->menuItem|in_array:$_activeMenuItems} active{/if}">
					<span class="icon icon24 {$_sectionMenuItem->icon}"></span>
					<span class="menuOverlayItemTitle">{@$_sectionMenuItem}</span>
				</a>
				<ol class="menuOverlayItemList">
					{foreach from=$__wcf->getACPMenu()->getMenuItems($_sectionMenuItem->menuItem) item=_menuItemCategory}
						<li class="menuOverlayTitle">{@$_menuItemCategory}</li>
						{foreach from=$__wcf->getACPMenu()->getMenuItems($_menuItemCategory->menuItem) item=_menuItem}
							{assign var=_subMenuItems value=$__wcf->getACPMenu()->getMenuItems($_menuItem->menuItem)}
							
							{if $_subMenuItems|empty}
								<li class="menuOverlayItem{if $_menuItem->menuItem|in_array:$_activeMenuItems} active{/if}"><a href="{$_menuItem->getLink()}" class="menuOverlayItemLink">{@$_menuItem}</a></li>
							{else}
								{if $_menuItem->menuItem === 'wcf.acp.menu.link.option.category'}
									{* handle special option categories *}
									{foreach from=$_subMenuItems item=_subMenuItem}
										<li class="menuOverlayItem{if $_subMenuItem->menuItem|in_array:$_activeMenuItems} active{/if}"><a href="{$_subMenuItem->getLink()}" class="menuOverlayItemLink">{@$_subMenuItem}</a></li>
									{/foreach}
								{else}
									<li class="menuOverlayItem">
										<a href="{$_menuItem->getLink()}" class="menuOverlayItemLink{if $_menuItem->menuItem|in_array:$_activeMenuItems && $_activeMenuItems[0] === $_menuItem->menuItem} active{/if}">{@$_menuItem}</a>
										
										{foreach from=$_subMenuItems item=_subMenuItem}
											<a href="{$_subMenuItem->getLink()}" class="menuOverlayItemLinkIcon{if $_subMenuItem->menuItem|in_array:$_activeMenuItems} active{/if}"><span class="icon icon24 {$_subMenuItem->icon}"></span></a>
										{/foreach}
									</li>
								{/if}
							{/if}
						{/foreach}
					{/foreach}
				</ol>
			</li>
		{/foreach}
	</ol>
</div>

{* user menu *}
<div id="pageUserMenuMobile" class="pageUserMenuMobile menuOverlayMobile" data-page-logo="{$__wcf->getPath()}images/default-logo.png">
	<ol class="menuOverlayItemList" data-title="{lang}wcf.menu.user{/lang}">
		<li class="menuOverlayTitle">{lang}wcf.menu.user{/lang}</li>
		<li class="menuOverlayItem">
			<a href="#" class="menuOverlayItemLink box24">
				<span class="icon icon24 fa-home"></span>
				<span class="menuOverlayItemTitle">{lang}wcf.global.jumpToPage{/lang}</span>
			</a>
			<ol class="menuOverlayItemList">
				{foreach from=$__wcf->getFrontendMenu()->getMenuItemNodeList() item=_menuItem}
					{if !$_menuItem->parentItemID && $_menuItem->getPage()}
						<li class="menuOverlayItem"><a href="{$_menuItem->getPage()->getLink()}" class="menuOverlayItemLink">{$_menuItem->getPage()}</a></li>
					{/if}
				{/foreach}
			</ol>
		</li>
		<li class="menuOverlayItem">
			<a href="#" class="menuOverlayItemLink box24">
				<span class="icon icon24 fa-info"></span>
				<span class="menuOverlayItemTitle">WoltLab&reg;</span>
			</a>
			<ol class="menuOverlayItemList">
				<li class="menuOverlayItem"><a href="{@$__wcf->getPath()}acp/dereferrer.php?url={"https://www.woltlab.com"|rawurlencode}" class="menuOverlayItemLink"{if EXTERNAL_LINK_TARGET_BLANK} target="_blank"{/if}>{lang}wcf.acp.index.woltlab.website{/lang}</a></li>
				<li class="menuOverlayItem"><a href="{@$__wcf->getPath()}acp/dereferrer.php?url={"https://community.woltlab.com"|rawurlencode}" class="menuOverlayItemLink"{if EXTERNAL_LINK_TARGET_BLANK} target="_blank"{/if}>{lang}wcf.acp.index.woltlab.forums{/lang}</a></li>
				<li class="menuOverlayItem"><a href="{@$__wcf->getPath()}acp/dereferrer.php?url={"https://www.woltlab.com/ticket-add/"|rawurlencode}" class="menuOverlayItemLink"{if EXTERNAL_LINK_TARGET_BLANK} target="_blank"{/if}>{lang}wcf.acp.index.woltlab.tickets{/lang}</a></li>
				<li class="menuOverlayItem"><a href="{@$__wcf->getPath()}acp/dereferrer.php?url={"https://pluginstore.woltlab.com"|rawurlencode}" class="menuOverlayItemLink"{if EXTERNAL_LINK_TARGET_BLANK} target="_blank"{/if}>{lang}wcf.acp.index.woltlab.pluginStore{/lang}</a></li>
			</ol>
		</li>
		<li class="menuOverlayTitle">{$__wcf->user->username}</li>
		<li class="menuOverlayItem">
			<a href="{link controller='Logout'}t={@SECURITY_TOKEN}{/link}" class="menuOverlayItemLink box24">
				<span class="icon icon24 fa-sign-out"></span>
				<span class="menuOverlayItemTitle">{lang}wcf.user.logout{/lang}</span>
			</a>
		</li>
	</ol>
</div>
