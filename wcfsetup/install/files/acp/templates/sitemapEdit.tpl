{include file='header' pageTitle='wcf.acp.sitemap.edit'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.sitemap.edit{/lang}</h1>
		<p class="contentHeaderDescription">{$sitemapObject->getName()}</p>
	</div>
	
	{hascontent}
		<nav class="contentHeaderNavigation">
			<ul>
				{content}
					{event name='contentHeaderNavigation'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</header>

{unsafe:$form->getHtml()}

{include file='footer'}
