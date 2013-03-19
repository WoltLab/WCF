{if $__wcf->getPageMenu()->getMenuItems('header')|count > 0}
	<nav id="mainMenu" class="mainMenu">
		<ul>{foreach from=$__wcf->getPageMenu()->getMenuItems('header') item=menuItem}<li{if $__wcf->getPageMenu()->getActiveMenuItem() == $menuItem->menuItem} class="active"{/if}><a href="{$menuItem->getProcessor()->getLink()}">{lang}{$menuItem->menuItem}{/lang}{if $menuItem->getProcessor()->getNotifications()} <span class="badge badgeUpdate">{#$menuItem->getProcessor()->getNotifications()}</span>{/if}</a></li>{/foreach}</ul>
	</nav>
{/if}
