{include file='header' pageTitle='wcf.acp.sitemap.edit'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.sitemap.edit{/lang}</h1>
		<p class="contentHeaderDescription">{$sitemapObject->getName()}</p>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='SitemapList'}{/link}" class="button">{icon name='list'} <span>{lang}wcf.acp.menu.link.maintenance.sitemap{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{unsafe:$form->getHtml()}

{include file='footer'}
