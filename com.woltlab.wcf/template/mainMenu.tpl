{if $__wcf->getPageMenu()->getMenuItems('header')|count > 0}
	<nav id="mainMenu" class="mainMenu">
		<span class="invisible">{lang}wcf.page.mainMenu{/lang}</span>
		<ul>{foreach from=$__wcf->getPageMenu()->getMenuItems('header') item=menuItem}<li{if $__wcf->getPageMenu()->getActiveMenuItem() == $menuItem->menuItem} class="active"{/if}>{*
			*}<a href="{$menuItem->getProcessor()->getLink()}">{lang}{$menuItem->menuItem}{/lang}{if $menuItem->getProcessor()->getNotifications()} <span class="badge badgeUpdate">{#$menuItem->getProcessor()->getNotifications()}</span>{/if}</a>{*
		*}{if $__wcf->getPageMenu()->getMenuItems($menuItem->menuItem)|count > 0 && $__wcf->getPageMenu()->getActiveMenuItem() == $menuItem->menuItem}<ul class="invisible">{*
				*}{foreach from=$__wcf->getPageMenu()->getMenuItems($menuItem->menuItem) item=subMenuItem}{*
					*}<li><a href="{$subMenuItem->getProcessor()->getLink()}"><span>{lang}{$subMenuItem->menuItem}{/lang}</span></a>{if $subMenuItem->getProcessor()->getNotifications()} <span class="badge badgeUpdate">{#$subMenuItem->getProcessor()->getNotifications()}</span>{/if}</li>{*
				*}{/foreach}{*
		*}</ul>{/if}</li>{/foreach}</ul>
	</nav>
{/if}
