{foreach from=$__wcf->getPageMenu()->getMenuItems($menuItem->menuItem) item=subMenuItem}
	<li{if $__wcf->getPageMenu()->getActiveMenuItem(1) == $subMenuItem->menuItem} class="active"{/if} data-menu-item="{$subMenuItem->menuItem}"><a href="{$subMenuItem->getProcessor()->getLink()}"><span>{lang}{$subMenuItem->menuItem}{/lang}</span></a>{if $subMenuItem->getProcessor()->getNotifications()} <span class="badge badgeUpdate">{#$subMenuItem->getProcessor()->getNotifications()}</span>{/if}</li>
{/foreach}

{event name='items'}
