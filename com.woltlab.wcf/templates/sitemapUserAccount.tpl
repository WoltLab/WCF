<ul class="sitemapList">
	{if $__wcf->getUser()->userID}
		{foreach from=$__wcf->getUserMenu()->getMenuItems('') item=menuCategory}
			<li>
				<h3>{lang}{$menuCategory->menuItem}{/lang}</h3>
				<ul>
					{foreach from=$__wcf->getUserMenu()->getMenuItems($menuCategory->menuItem) item=menuItem}
						<li><a href="{$menuItem->getLink()}">{lang}{$menuItem->menuItem}{/lang}</a></li>
					{/foreach}
				</ul>
			</li>
		{/foreach}
	{else}
		<li>
			<a href="{link controller='Login'}{/link}">{lang}wcf.user.login{/lang}</a>
		</li>
		{if !REGISTER_DISABLED}
			<li>
				<a href="{link controller='Register'}{/link}">{lang}wcf.user.register{/lang}</a>
			</li>
		{/if}
	{/if}
</ul>
