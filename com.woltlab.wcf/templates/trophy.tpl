{capture assign='pageTitle'}{$trophy->getTitle()}{if $pageNo > 1} - {lang}wcf.page.pageNo{/lang}{/if}{/capture}

{capture assign='headContent'}
	{if $pageNo < $pages}
		<link rel="next" href="{link controller='Trophy' object=$trophy pageNo=$pageNo+1}{/link}">
	{/if}
	{if $pageNo > 1}
		<link rel="prev" href="{link controller='Trophy' object=$trophy}{if $pageNo > 2}pageNo={$pageNo-1}{/if}{/link}">
	{/if}
{/capture}

{capture assign='contentHeader'}
	<header class="contentHeader messageGroupContentHeader">
		<div class="contentHeaderIcon">
			{unsafe:$trophy->renderTrophy(64)}
		</div>

		<div class="contentHeaderTitle">
			<h1 class="contentTitle">{$trophy->getTitle()}</h1>
			<ul class="inlineList contentHeaderMetaData">
				{if !$trophy->getDescription()|empty}<li>{unsafe:$trophy->getDescription()}</li>{/if}
				<li>
					{icon name='users'}
					<span>{lang}wcf.user.trophy.trophyAwarded{/lang}</span>
				</li>
			</ul>
		</div>
	</header>
{/capture}

{capture assign='contentInteractionPagination'}
	{if $pages > 1}
		<woltlab-core-pagination
			page="{$pageNo}"
			count="{$pages}"
			url="{link controller='Trophy' object=$trophy}{/link}"
		></woltlab-core-pagination>
	{/if}
{/capture}

{include file='header'}

{if $objects|count}
	<div class="section sectionContainerList">
		<ol class="containerList trophyCategoryList doubleColumned">
			{foreach from=$objects item=userTrophy}
				<li class="box64">
					<div>{unsafe:$userTrophy->getUserProfile()->getAvatar()->getImageTag(64)}</div>
	
					<div class="containerHeadline">
						<h3>{user object=$userTrophy->getUserProfile()}</h3>
						<small>{if !$userTrophy->getDescription()|empty}<span class="separatorRight">{unsafe:$userTrophy->getDescription()}</span> {/if}{@$userTrophy->time|time}</small>
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
				url="{link controller='Trophy' object=$trophy}{/link}"
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
