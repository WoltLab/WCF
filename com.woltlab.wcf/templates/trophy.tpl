{capture assign='pageTitle'}{$trophy->getTitle()}{if $pageNo > 1} - {lang}wcf.page.pageNo{/lang}{/if}{/capture}

{capture assign='headContent'}
	{if $pageNo < $pages}
		<link rel="next" href="{link controller='Trophy' object=$trophy}pageNo={@$pageNo+1}{/link}">
	{/if}
	{if $pageNo > 1}
		<link rel="prev" href="{link controller='Trophy' object=$trophy}{if $pageNo > 2}pageNo={@$pageNo-1}{/if}{/link}">
	{/if}
{/capture}

{capture assign='contentHeader'}
	<header class="contentHeader messageGroupContentHeader">
		<div class="contentHeaderIcon">
			{@$trophy->renderTrophy(64)}
		</div>

		<div class="contentHeaderTitle">
			<h1 class="contentTitle">{$trophy->getTitle()}</h1>
			<ul class="inlineList contentHeaderMetaData">
				{if !$trophy->getDescription()|empty}<li>{@$trophy->getDescription()}</li>{/if}
				<li>
					<span class="icon icon16 fa-users"></span>
					<span>{lang}wcf.user.trophy.trophyAwarded{/lang}</span>
				</li>
			</ul>
		</div>
	</header>
{/capture}

{include file='header'}

{hascontent}
	<div class="paginationTop">
		{content}
			{pages print=true assign='pagesLinks' controller='Trophy' object=$trophy link="pageNo=%d"}
		{/content}
	</div>
{/hascontent}

{if $objects|count}
	<ol class="section containerList trophyCategoryList tripleColumned">
		{foreach from=$objects item=userTrophy}
			<li class="box64">
				<div>{@$userTrophy->getUserProfile()->getAvatar()->getImageTag(64)}</div>

				<div class="sidebarItemTitle">
					<h3>{@$userTrophy->getUserProfile()->getAnchorTag()}</h3>
					<small>{if !$userTrophy->getDescription()|empty}<span class="separatorRight">{@$userTrophy->getDescription()}</span> {/if}{@$userTrophy->time|time}</small>
				</div>
			</li>
		{/foreach}
	</ol>
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