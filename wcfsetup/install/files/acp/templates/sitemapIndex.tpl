<?xml version="1.0" encoding="UTF-8"?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
	{foreach from=$sitemaps item="sitemap"}
		<sitemap>
			<loc>{$sitemap}</loc>
			<lastmod>{TIME_NOW|date:"c"}</lastmod>
		</sitemap>
	{/foreach}
</sitemapindex>
