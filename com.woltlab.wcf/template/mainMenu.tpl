{if $__wcf->getPageMenu()->getMenuItems('header')|count > 0}
	<nav id="mainMenu" class="mainMenu">
		<ul>
			{foreach from=$__wcf->getPageMenu()->getMenuItems('header') item=menuItem}
				<li{if $__wcf->getPageMenu()->getActiveMenuItem() == $menuItem->menuItem} class="activeMenuItem"{/if}><a href="{$menuItem->menuItemLink}{if $menuItem->menuItemLink|strpos:'?' !== false}{@SID_ARG_2ND}{else}{@SID_ARG_1ST}{/if}">{lang}{$menuItem->menuItem}{/lang}{if $menuItem->getProcessor()->getNotifications()} <span class="badge">{#$menuItem->getProcessor()->getNotifications()}</span>{/if}</a> 
				
				{if $__wcf->getPageMenu()->getMenuItems($menuItem->menuItem)|count > 0}
					<ul>
						{foreach from=$__wcf->getPageMenu()->getMenuItems($menuItem->menuItem) item=subMenuItem}
							<li><a href="{$subMenuItem->menuItemLink}{if $subMenuItem->menuItemLink|strpos:'?' !== false}{@SID_ARG_2ND}{else}{@SID_ARG_1ST}{/if}">{lang}{$subMenuItem->menuItem}{/lang}</a>{if $subMenuItem->getProcessor()->getNotifications()} <span class="badge">{#$subMenuItem->getProcessor()->getNotifications()}</span>{/if}</li>
						{/foreach}
					</ul>
				{/if}
				
				</li>
			{/foreach}
		</ul>
	</nav>
{/if}
