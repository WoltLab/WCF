{if PACKAGE_ID && $__wcf->user->userID}
	{assign var=_activeMenuItems value=$__wcf->getACPMenu()->getActiveMenuItems()}
	
	<nav id="acpPageMenu" class="acpPageMenu">
		<ol class="acpPageMenuList">
			{foreach from=$__wcf->getACPMenu()->getMenuItems('') item=_sectionMenuItem}
				<li>
					<a href="#" class="acpPageMenuLink{if $_sectionMenuItem->menuItem|in_array:$_activeMenuItems} active{/if}" data-menu-item="{$_sectionMenuItem->menuItem}">
						<span class="icon icon48 {$_sectionMenuItem->icon}"></span>
						<span class="acpPageMenuItemLabel">{@$_sectionMenuItem}</span>
					</a>
				</li>
			{/foreach}
		</ol>
	</nav>
	
	<nav id="acpPageSubMenu" class="acpPageSubMenu">
		{foreach from=$__wcf->getACPMenu()->getMenuItems('') item=_sectionMenuItem}
			<ol class="acpPageSubMenuCategoryList{if $_sectionMenuItem->menuItem|in_array:$_activeMenuItems} active{/if}" data-menu-item="{$_sectionMenuItem->menuItem}">
				{foreach from=$__wcf->getACPMenu()->getMenuItems($_sectionMenuItem->menuItem) item=_menuItemCategory}
					<li class="acpPageSubMenuCategory">
						<span>{@$_menuItemCategory}</span>
						
						<ol class="acpPageSubMenuItemList">
							{foreach from=$__wcf->getACPMenu()->getMenuItems($_menuItemCategory->menuItem) item=_menuItem}
								{assign var=_subMenuItems value=$__wcf->getACPMenu()->getMenuItems($_menuItem->menuItem)}
								
								{if $_subMenuItems|empty}
									<li{if $_menuItem->menuItem|in_array:$_activeMenuItems} class="active"{/if}><a href="{$_menuItem->getLink()}" class="acpPageSubMenuLink">{@$_menuItem}</a></li>
								{else}
									{if $_menuItem->menuItem === 'wcf.acp.menu.link.option.category'}
										{* handle special option categories *}
										{foreach from=$_subMenuItems item=_subMenuItem}
											<li{if $_subMenuItem->menuItem|in_array:$_activeMenuItems} class="active"{/if}><a href="{$_subMenuItem->getLink()}" class="acpPageSubMenuLink">{@$_subMenuItem}</a></li>
										{/foreach}
									{else}
										<li class="acpPageSubMenuLinkWrapper">
											<a href="{$_menuItem->getLink()}" class="acpPageSubMenuLink{if $_menuItem->menuItem|in_array:$_activeMenuItems && $_activeMenuItems[0] === $_menuItem->menuItem} active{/if}">{@$_menuItem}</a>
											
											{foreach from=$_subMenuItems item=_subMenuItem}
												<a href="{$_subMenuItem->getLink()}" class="acpPageSubMenuIcon jsTooltip{if $_subMenuItem->menuItem|in_array:$_activeMenuItems} active{/if}" title="{@$_subMenuItem}"><span class="icon icon16 {$_subMenuItem->icon}"></span></a>
											{/foreach}
										</li>
									{/if}
								{/if}
							{/foreach}
						</ol>
					</li>
				{/foreach}
			</ol>
		{/foreach}
	</nav>
{/if}
