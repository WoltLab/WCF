{capture assign='contentHeader'}
	<header class="contentHeader">
		<div class="contentHeaderTitle">
			<h1 class="contentTitle">{if $query}<a href="{link controller='Search'}q={$query|urlencode}{/link}">{$__wcf->getActivePage()->getTitle()}</a>{else}{$__wcf->getActivePage()->getTitle()}{/if}</h1>
			<p class="contentHeaderDescription">{lang}wcf.search.results.description{/lang}</p>
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
{/capture}

{capture assign='contentInteractionPagination'}
	{assign var=encodedHighlight value=$highlight|urlencode}
	{pages print=true application=$application assign=pagesLinks controller='SearchResult' id=$searchID link="pageNo=%d&highlight=$encodedHighlight"}
{/capture}

{capture assign='contentInteractionButtons'}
	{if $alterable}
		<a href="{link controller='Search'}modify={@$searchID}{/link}" class="contentInteractionButton button small">{lang}wcf.search.results.change{/lang}</a>
	{/if}
{/capture}

{include file='header'}

{include file=$resultListTemplateName application=$resultListApplication}

<footer class="contentFooter">
	{hascontent}
		<div class="paginationBottom">
			{content}{@$pagesLinks}{/content}
		</div>
	{/hascontent}
	
	{hascontent}
		<nav class="contentFooterNavigation">
			<ul>
				{content}
					{if $alterable}
						<li><a href="{link controller='Search'}modify={@$searchID}{/link}" class="button"><span class="icon icon16 fa-search"></span> <span>{lang}wcf.search.results.change{/lang}</span></a></li>
					{/if}
					{event name='contentFooterNavigation'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</footer>

{include file='footer'}
