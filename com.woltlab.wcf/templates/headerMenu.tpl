{if $__wcf->getPageMenu()->getMenuItems('header')|count > 0}
	<div id="mainMenu" class="mainMenu">
		<div class="mainMenuInner">
			<ul>
				{foreach from=$__wcf->getPageMenu()->getMenuItems('header') item=menuItem}
					<li><a href="{$menuItem->menuItemLink}">{$menuItem->menuItem} ({#$menuItem->getProvider()->getNotifications()})</a>
					
					{if $__wcf->getPageMenu()->getMenuItems($menuItem->menuItem)|count > 0}
						<ul>
							{foreach from=$__wcf->getPageMenu()->getMenuItems($menuItem->menuItem) item=subMenuItem}
								<li><a href="{$subMenuItem->menuItemLink}">{$subMenuItem->menuItem} ({#$subMenuItem->getProvider()->getNotifications()})</a></li>
							{/foreach}
						</ul>
					{/if}
					
					</li>
				{/foreach}
			</ul>
		</div>
	</div>
{/if}
