{foreach from=$__wcf->getPageMenu()->getMenuItems('header') item=menuItem}
	{if $__wcf->getPageMenu()->getMenuItems($menuItem->menuItem)|count > 0}
		<div class="wcf-menu">
			<ul>
				{foreach from=$__wcf->getPageMenu()->getMenuItems($menuItem->menuItem) item=subMenuItem}
					<li><a href="{$subMenuItem->getProcessor()->getLink()}" title="{lang}{$subMenuItem->menuItem}{/lang}"><span>{lang}{$subMenuItem->menuItem}{/lang}</span></a>{if $subMenuItem->getProcessor()->getNotifications()} <span class="wcf-badge">{#$subMenuItem->getProcessor()->getNotifications()}</span>{/if}</li>
				{/foreach}
			</ul>
		</div>
	{/if}
{/foreach}