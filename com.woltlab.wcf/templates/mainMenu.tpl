{if $__wcf->getPageMenu()->getMenuItems('header')|count > 0}
	<nav id="mainMenu" class="mainMenu jsMobileNavigation" data-button-label="{lang}wcf.page.mainMenu{/lang}">
		<ul>
			{foreach from=$__wcf->getPageMenu()->getMenuItems('header') item=menuItem}
				<li{if $__wcf->getPageMenu()->getActiveMenuItem() == $menuItem->menuItem} class="active"{/if}>
					<a href="{$menuItem->getProcessor()->getLink()}">{lang}{$menuItem->menuItem}{/lang}{if $menuItem->getProcessor()->getNotifications()} <span class="badge badgeUpdate">{#$menuItem->getProcessor()->getNotifications()}</span>{/if}</a>
					{if $__wcf->getPageMenu()->getMenuItems($menuItem->menuItem)|count > 0 && $__wcf->getPageMenu()->getActiveMenuItem() == $menuItem->menuItem}
						<ul class="invisible">
							{foreach from=$__wcf->getPageMenu()->getMenuItems($menuItem->menuItem) item=subMenuItem}
								<li{if $__wcf->getPageMenu()->getActiveMenuItem(1) == $subMenuItem->menuItem} class="active"{/if}><a href="{$subMenuItem->getProcessor()->getLink()}"><span>{lang}{$subMenuItem->menuItem}{/lang}</span></a>{if $subMenuItem->getProcessor()->getNotifications()} <span class="badge badgeUpdate">{#$subMenuItem->getProcessor()->getNotifications()}</span>{/if}</li>
							{/foreach}
							{event name='items'}
						</ul>
					{/if}
				</li>
			{/foreach}
		</ul>
	</nav>
{/if}
