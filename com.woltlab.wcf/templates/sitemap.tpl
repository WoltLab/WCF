<div class="sitemap">
	{hascontent}
		<div class="section tabMenuContainer" data-active="sitemap_{@$defaultSitemapName}">
			<nav class="tabMenu">
				<ul>
					{content}
						{foreach from=$tree item=sitemapName}
							<li><a href="#sitemap_{$sitemapName}" class="sitemapNavigation" data-sitemap-name="{$sitemapName}">{lang}wcf.page.sitemap.{$sitemapName}{/lang}</a></li>
						{/foreach}
					{/content}
				</ul>
			</nav>
			
			{foreach from=$tree item=sitemapName}
				<div id="sitemap_{$sitemapName}" class="tabMenuContent hidden">
					{if $sitemapName == $defaultSitemapName}{@$sitemap}{/if}
				</div>
			{/foreach}
		</div>
	{hascontentelse}
		{@$sitemap}
	{/hascontent}
</div>