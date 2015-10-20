{if $__wcf->getPageMenu()->getMenuItems('header')|count > 0}
	<nav id="mainMenu" class="mainMenu jsMobileNavigation" data-button-label="{lang}wcf.page.mainMenu{/lang}">
		<ul>
			{foreach from=$__wcf->getPageMenu()->getMenuItems('header') item=menuItem}
				<li class="{if $__wcf->getPageMenu()->getActiveMenuItem() == $menuItem->menuItem}active {/if}{if $__wcf->getPageMenu()->getMenuItems($menuItem->menuItem)|count > 0}subMenuItems{/if}" data-menu-item="{$menuItem->menuItem}">
					<a href="{$menuItem->getProcessor()->getLink()}">{lang}{$menuItem->menuItem}{/lang}{if $menuItem->getProcessor()->getNotifications()} <span class="badge badgeUpdate">{#$menuItem->getProcessor()->getNotifications()}</span>{/if}</a>
					{if $__wcf->getPageMenu()->getMenuItems($menuItem->menuItem)|count > 0}
						<ul class="subMenu">
							{include file='mainMenuSubMenu'}
						</ul>
					{/if}
				</li>
			{/foreach}
		</ul>
	</nav>
{/if}
