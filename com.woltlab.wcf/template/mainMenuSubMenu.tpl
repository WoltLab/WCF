{foreach from=$__wcf->getPageMenu()->getMenuItems('header') item=menuItem}
	{if $__wcf->getPageMenu()->getMenuItems($menuItem->menuItem)|count > 0 && $__wcf->getPageMenu()->getActiveMenuItem() == $menuItem->menuItem}
		<ul>
			{foreach from=$__wcf->getPageMenu()->getMenuItems($menuItem->menuItem) item=subMenuItem}
				<li><a href="{$subMenuItem->getProcessor()->getLink()}"><span>{lang}{$subMenuItem->menuItem}{/lang}</span></a>{if $subMenuItem->getProcessor()->getNotifications()} <span class="wcf-badge">{#$subMenuItem->getProcessor()->getNotifications()}</span>{/if}</li>
			{/foreach}
		</ul>
	{/if}
{/foreach}