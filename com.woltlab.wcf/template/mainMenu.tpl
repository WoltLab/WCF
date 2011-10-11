{if $__wcf->getPageMenu()->getMenuItems('header')|count > 0}
	<nav id="mainMenu" class="mainMenu">
		<ul>
			{foreach from=$__wcf->getPageMenu()->getMenuItems('header') item=menuItem}
				<li{if $__wcf->getPageMenu()->getActiveMenuItem() == $menuItem->menuItem} class="activeMenuItem"{/if}><a href="{$menuItem->menuItemLink}">{$menuItem->menuItem}</a> <span class="badge">{#$menuItem->getProcessor()->getNotifications()}</span>
				
				{if $__wcf->getPageMenu()->getMenuItems($menuItem->menuItem)|count > 0}
					<ul>
						{foreach from=$__wcf->getPageMenu()->getMenuItems($menuItem->menuItem) item=subMenuItem}
							<li><a href="{$subMenuItem->menuItemLink}">{$subMenuItem->menuItem}</a> <span class="badge">{#$subMenuItem->getProcessor()->getNotifications()}</span></li>
						{/foreach}
					</ul>
				{/if}
				
				</li>
			{/foreach}
		</ul>
	</nav>
{/if}
