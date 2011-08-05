{if $__wcf->getPageMenu()->getMenuItems('footer')|count > 0}
	<nav id="footerMenu" class="footerMenu">
		<ul>
			{foreach from=$__wcf->getPageMenu()->getMenuItems('footer') item=menuItem}
				<li><a href="{$menuItem->menuItemLink}">{$menuItem->menuItem} ({#$menuItem->getProcessor()->getNotifications()})</a></li>
			{/foreach}
		</ul>
	</nav>
{/if}