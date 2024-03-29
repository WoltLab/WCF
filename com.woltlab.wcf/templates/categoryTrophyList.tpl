{capture assign='pageTitle'}{$category->getTitle()}{if $pageNo > 1} - {lang}wcf.page.pageNo{/lang}{/if}{/capture}

{capture assign='contentHeader'}
	<header class="contentHeader messageGroupContentHeader">
		<div class="contentHeaderTitle">
			<h1 class="contentTitle">{$category->getTitle()}</h1>
			{if $category && $category->getDescription()}
				<p class="contentHeaderDescription">{if $category->descriptionUseHtml}{@$category->getDescription()}{else}{$category->getDescription()}{/if}</p>
			{/if}
		</div>
	</header>
{/capture}

{capture assign='headContent'}
	{if $pageNo < $pages}
		<link rel="next" href="{link controller='CategoryTrophyList' object=$category}pageNo={@$pageNo+1}{/link}">
	{/if}
	{if $pageNo > 1}
		<link rel="prev" href="{link controller='CategoryTrophyList' object=$category}{if $pageNo > 2}pageNo={@$pageNo-1}{/if}{/link}">
	{/if}
{/capture}

{capture assign='contentInteractionPagination'}
	{pages print=true assign='pagesLinks' controller='CategoryTrophyList' object=$category link="pageNo=%d"}
{/capture}

{include file='header'}

{if $objects|count}
	<div class="section sectionContainerList">
		<ol class="containerList trophyCategoryList doubleColumned">
			{foreach from=$objects item=trophy}
				<li class="box64">
					<div>{@$trophy->renderTrophy(64)}</div>
					
					<div class="containerHeadline">
						<h3><a href="{$trophy->getLink()}">{@$trophy->getTitle()}</a></h3>
						{if !$trophy->getDescription()|empty}<p><small>{@$trophy->getDescription()}</small></p>{/if}
						<p><small>{lang items=$trophy->awarded}wcf.user.trophy.trophyAwarded{/lang}</small></p>
					</div>
				</li>
			{/foreach}
		</ol>
	</div>
{else}
	<woltlab-core-notice type="info">{lang}wcf.global.noItems{/lang}</woltlab-core-notice>
{/if}

<footer class="contentFooter">
	{hascontent}
		<div class="paginationBottom">
			{content}{@$pagesLinks}{/content}
		</div>
	{/hascontent}

	{hascontent}
		<nav class="contentFooterNavigation">
			<ul>
				{content}{event name='contentFooterNavigation'}{/content}
			</ul>
		</nav>
	{/hascontent}
</footer>

{include file='footer'}
