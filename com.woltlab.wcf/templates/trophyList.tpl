{capture assign='pageTitle'}{$category->getTitle()}{if $pageNo > 1} - {lang}wcf.page.pageNo{/lang}{/if}{/capture}

{capture assign='contentHeader'}
	<header class="contentHeader messageGroupContentHeader">
		<div class="contentHeaderTitle">
			<h1 class="contentTitle">{$category->getTitle()}{if $items} <span class="badge badgeInverse">{#$items}</span>{/if}</h1>
			<ul class="inlineList contentHeaderMetaData">
				<li>
					{$category->getDescription()}
				</li>
			</ul>
		</div>
	</header>
{/capture}

{capture assign='headContent'}
	{if $pageNo < $pages}
		<link rel="next" href="{link controller='TrophyList' object=$category}pageNo={@$pageNo+1}{/link}">
	{/if}
	{if $pageNo > 1}
		<link rel="prev" href="{link controller='TrophyList' object=$category}{if $pageNo > 2}pageNo={@$pageNo-1}{/if}{/link}">
	{/if}
{/capture}

{include file='header'}

{hascontent}
	<div class="paginationTop">
		{content}
			{pages print=true assign='pagesLinks' controller='TrophyList' object=$category link="pageNo=%d"}
		{/content}
	</div>
{/hascontent}

<div class="section">
	<nav class="tabMenu">
		<ul>
			{foreach from=$categories item='menuCategory'}
				<li{if $menuCategory->categoryID == $category->categoryID} class="active"{/if}><a href="{$menuCategory->getLink()}">{$menuCategory->getTitle()}</a></li>
			{/foreach}
		</ul>
	</nav>

	<div class="tabMenuContent">
		{if $objects|count}
			<ol class="section containerBoxList trophyCategoryList tripleColumned">
				{foreach from=$objects item=trophy}
					<li class="box64">
						<div>{@$trophy->renderTrophy(64)}</div>

						<div class="sidebarItemTitle">
							<h3><a href="{$trophy->getLink()}">{@$trophy->getTitle()}</a></h3>
							<small>{@$trophy->getDescription()}</small>
						</div>
					</li>
				{/foreach}
			</ol>
		{else}
			<p class="info">{lang}wcf.global.noItems{/lang}</p>
		{/if}
	</div>
</div>

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