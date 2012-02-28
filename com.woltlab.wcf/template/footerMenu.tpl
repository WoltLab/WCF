{if $__wcf->getPageMenu()->getMenuItems('footer')|count > 0}
	<div class="wcf-menu">
		<ul>
			{foreach from=$__wcf->getPageMenu()->getMenuItems('footer') item=menuItem}
				<li><a href="{$menuItem->getProcessor()->getLink()}">{lang}{$menuItem->menuItem}{/lang}{if $menuItem->getProcessor()->getNotifications()} <span class="wcf-badge">{#$menuItem->getProcessor()->getNotifications()}</span>{/if}</a></li>
			{/foreach}
		</ul>
	</div>
{/if}
