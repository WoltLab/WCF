<div class="sitemap">
	{hascontent}
		<div class="tabMenuContainer" data-active="sitemap_{@$defaultSitemapName}">
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
				<div id="sitemap_{$sitemapName}" class="container containerPadding tabMenuContent hidden">
					{if $sitemapName == $defaultSitemapName}{@$sitemap}{/if}
				</div>
			{/foreach}
		</div>
		
		<script type="text/javascript">
			//<![CDATA[
			$(function() {
				// fix anchor
				var $location = location.toString().replace(location.hash, '');
				$('.sitemap .tabMenu a').each(function(index, link) {
					var $link = $(link);
					$link.attr('href', $location + $link.attr('href'));
				});
				
				WCF.TabMenu.init();
			});
			//]]>
		</script>
	{hascontentelse}
		{@$sitemap}
	{/hascontent}
</div>