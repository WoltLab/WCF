{foreach from=$__wcf->getPageMenu()->getMenuItems('header') item=menuItem}
	{if $__wcf->getPageMenu()->getMenuItems($menuItem->menuItem)|count > 0}
		<ul class="wcf-subMenu">
			{foreach from=$__wcf->getPageMenu()->getMenuItems($menuItem->menuItem) item=subMenuItem}
				<li><a href="{$subMenuItem->getProcessor()->getLink()}" title="{lang}{$subMenuItem->menuItem}{/lang}">{lang}{$subMenuItem->menuItem}{/lang}</a>{if $subMenuItem->getProcessor()->getNotifications()} <span class="wcf-badge">{#$subMenuItem->getProcessor()->getNotifications()}</span>{/if}</li>
			{/foreach}
		</ul>
	{/if}
{/foreach}