{foreach from=$__wcf->getPageMenu()->getMenuItems('header') item=menuItem}
	{if $__wcf->getPageMenu()->getMenuItems($menuItem->menuItem)|count > 0}
		<ul>
			{foreach from=$__wcf->getPageMenu()->getMenuItems($menuItem->menuItem) item=subMenuItem}
				<li><a href="{$subMenuItem->getProcessor()->getLink()}">{lang}{$subMenuItem->menuItem}{/lang}</a>{if $subMenuItem->getProcessor()->getNotifications()} <span class="badge">{#$subMenuItem->getProcessor()->getNotifications()}</span>{/if}</li>
			{/foreach}
		</ul>
	{/if}
{/foreach}