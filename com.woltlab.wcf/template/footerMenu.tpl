{if $__wcf->getPageMenu()->getMenuItems('footer')|count > 0}
	<ul>
		{foreach from=$__wcf->getPageMenu()->getMenuItems('footer') item=menuItem}
			<li><a href="{$menuItem->getProcessor()->getLink()}">{lang}{$menuItem->menuItem}{/lang}{if $menuItem->getProcessor()->getNotifications()} <span class="badge badgeUpdate">{#$menuItem->getProcessor()->getNotifications()}</span>{/if}</a></li>
		{/foreach}
	</ul>
{/if}
