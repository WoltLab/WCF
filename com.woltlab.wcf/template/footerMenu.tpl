<nav id="footerNavigation" class="footerNavigation">
	<ul>
		<li id="toTopLink" class="toTopLink"><a href="#top" title="{lang}wcf.global.scrollUp{/lang}" class="balloonTooltip"><img src="{icon size='S'}toTop{/icon}" alt="" /> <span class="invisible">{lang}wcf.global.scrollUp{/lang}</span></a></li>
		{if $__wcf->getPageMenu()->getMenuItems('footer')|count > 0}
			{foreach from=$__wcf->getPageMenu()->getMenuItems('footer') item=menuItem}
				<li><a href="{$menuItem->getProcessor()->getLink()}">{lang}{$menuItem->menuItem}{/lang}{if $menuItem->getProcessor()->getNotifications()} <span class="badge">{#$menuItem->getProcessor()->getNotifications()}</span>{/if}</a></li>
			{/foreach}
		{/if}
	</ul>
</nav>
