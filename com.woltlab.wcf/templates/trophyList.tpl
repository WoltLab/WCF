{capture assign='contentHeader'}
	<header class="contentHeader messageGroupContentHeader">
		<div class="contentHeaderTitle">
			<h1 class="contentTitle">{$__wcf->getActivePage()->getTitle()}</h1>
		</div>
	</header>
{/capture}

{capture assign='headContent'}
	{if $pageNo < $pages}
		<link rel="next" href="{link controller='TrophyList'}pageNo={@$pageNo+1}{/link}">
	{/if}
	{if $pageNo > 1}
		<link rel="prev" href="{link controller='TrophyList'}{if $pageNo > 2}pageNo={@$pageNo-1}{/if}{/link}">
	{/if}
{/capture}

{include file='header'}

{hascontent}
	<div class="paginationTop">
		{content}
			{pages print=true assign='pagesLinks' controller='TrophyList' link="pageNo=%d"}
		{/content}
	</div>
{/hascontent}

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
	<p class="info" role="status">{lang}wcf.global.noItems{/lang}</p>
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
