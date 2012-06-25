<div class="sitemap">
	{hascontent}
		<div class="tabMenuContainer" data-active="sitemap-{@$defaultSitemapName}">
			<nav class="tabMenu">
				<ul>
					{content}
						{foreach from=$tree item=sitemapName}
							<li><a href="#sitemap-{$sitemapName}" class="sitemapNavigation" data-sitemap-name="{$sitemapName}">{lang}wcf.sitemap.{$sitemapName}{/lang}</a></li>
						{/foreach}
					{/content}
				</ul>
			</nav>
			
			{foreach from=$tree item=sitemapName}
				<div id="sitemap-{$sitemapName}" class="container containerPadding tabMenuContent hidden">
					{if $sitemapName == $defaultSitemapName}{@$sitemap}{/if}
				</div>
			{/foreach}
		</div>
		
		<script type="text/javascript">
			//<![CDATA[
			$(function() {
				WCF.TabMenu.init();
			});
			//]]>
		</script>
	{hascontentelse}
		{@$sitemap}
	{/hascontent}
</div>