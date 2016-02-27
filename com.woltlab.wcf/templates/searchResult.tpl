{include file='documentHeader'}

<head>
	<title>{lang}wcf.search.results{/lang} - {PAGE_TITLE|language}</title>
	
	{include file='headInclude'}
</head>

<body id="tpl{$templateName|ucfirst}" data-template="{$templateName}" data-application="{$templateNameApplication}">

{include file='header'}

<header class="contentHeader">
	<h1 class="contentTitle">{if $query}<a href="{link controller='Search'}q={$query|urlencode}{/link}">{lang}wcf.search.results{/lang}</a>{else}{lang}wcf.search.results{/lang}{/if}</h1>
	<p class="contentHeaderDescription">{lang}wcf.search.results.description{/lang}</p>
</header>

{include file='userNotice'}

<div class="contentNavigation">
	{assign var=encodedHighlight value=$highlight|urlencode}
	{pages print=true application=$application assign=pagesLinks controller='SearchResult' id=$searchID link="pageNo=%d&highlight=$encodedHighlight"}
	
	{hascontent}
		<nav>
			<ul>
				{content}
					{if $alterable}
						<li><a href="{link controller='Search'}modify={@$searchID}{/link}" class="button"><span class="icon icon16 fa-search"></span> <span>{lang}wcf.search.results.change{/lang}</span></a></li>
					{/if}
					{event name='contentNavigationButtonsTop'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</div>

{include file=$resultListTemplateName application=$resultListApplication}

<div class="contentNavigation">
	{@$pagesLinks}
	
	{hascontent}
		<nav>
			<ul>
				{content}
					{if $alterable}
						<li><a href="{link controller='Search'}modify={@$searchID}{/link}" class="button"><span class="icon icon16 fa-search"></span> <span>{lang}wcf.search.results.change{/lang}</span></a></li>
					{/if}
					{event name='contentNavigationButtonsBottom'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</div>

{include file='footer'}

</body>
</html>