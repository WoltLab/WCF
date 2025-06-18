{capture assign='contentHeader'}
	<header class="contentHeader messageGroupContentHeader">
		<div class="contentHeaderTitle">
			<h1 class="contentTitle">{$__wcf->getActivePage()->getTitle()}</h1>
		</div>
	</header>
{/capture}

{capture assign='headContent'}
	{if $pageNo < $pages}
		<link rel="next" href="{link controller='TrophyList' pageNo=$pageNo+1}{/link}">
	{/if}
	{if $pageNo > 1}
		<link rel="prev" href="{link controller='TrophyList'}{if $pageNo > 2}pageNo={$pageNo-1}{/if}{/link}">
	{/if}
{/capture}

{capture assign='contentInteractionPagination'}
	{if $pages > 1}
		<woltlab-core-pagination
			page="{$pageNo}"
			count="{$pages}"
			url="{link controller='TrophyList'}{/link}"
		></woltlab-core-pagination>
	{/if}
{/capture}

{include file='header'}

{if $objects|count}
	<div class="section sectionContainerList">
		<ol class="containerList trophyCategoryList doubleColumned">
			{foreach from=$objects item=trophy}
				<li class="box64">
					<div>{unsafe:$trophy->renderTrophy(64)}</div>
					
					<div class="containerHeadline">
						<h3><a href="{$trophy->getLink()}">{unsafe:$trophy->getTitle()}</a></h3>
						{if !$trophy->getDescription()|empty}<p><small>{unsafe:$trophy->getDescription()}</small></p>{/if}
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
	{if $pages > 1}
		<div class="paginationBottom">
			<woltlab-core-pagination
				page="{$pageNo}"
				count="{$pages}"
				url="{link controller='TrophyList'}{/link}"
			></woltlab-core-pagination>
		</div>
	{/if}

	{hascontent}
		<nav class="contentFooterNavigation">
			<ul>
				{content}{event name='contentFooterNavigation'}{/content}
			</ul>
		</nav>
	{/hascontent}
</footer>

{include file='footer'}
