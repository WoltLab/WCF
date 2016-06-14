{capture assign='pageTitle'}{$category->getTitle()}{/capture}

{capture assign='contentTitle'}{$category->getTitle()}{/capture}

{capture assign='headContent'}
	{if $pageNo < $pages}
		<link rel="next" href="{link controller='CategoryArticleList' object=$category}pageNo={@$pageNo+1}{/link}">
	{/if}
	{if $pageNo > 1}
		<link rel="prev" href="{link controller='CategoryArticleList' object=$category}{if $pageNo > 2}pageNo={@$pageNo-1}{/if}{/link}">
	{/if}
	
	{if $__wcf->getUser()->userID}
		<link rel="alternate" type="application/rss+xml" title="{lang}wcf.global.button.rss{/lang}" href="{link controller='ArticleFeed' id=$categoryID appendSession=false}at={@$__wcf->getUser()->userID}-{@$__wcf->getUser()->accessToken}{/link}">
	{else}
		<link rel="alternate" type="application/rss+xml" title="{lang}wcf.global.button.rss{/lang}" href="{link controller='ArticleFeed' id=$categoryID appendSession=false}{/link}">
	{/if}
{/capture}

{capture assign='headerNavigation'}
	<li><a rel="alternate" href="{if $__wcf->getUser()->userID}{link controller='ArticleFeed' id=$categoryID appendSession=false}at={@$__wcf->getUser()->userID}-{@$__wcf->getUser()->accessToken}{/link}{else}{link controller='ArticleFeed' id=$categoryID appendSession=false}{/link}{/if}" title="{lang}wcf.global.button.rss{/lang}" class="jsTooltip"><span class="icon icon16 fa-rss"></span> <span class="invisible">{lang}wcf.global.button.rss{/lang}</span></a></li>
{/capture}

{if $__wcf->getSession()->getPermission('admin.content.article.canManageArticle')}
	{capture assign='contentHeaderNavigation'}
		<li><a href="{link controller='ArticleAdd' isACP=true}categoryID={@$category->categoryID}{/link}" class="button"><span class="icon icon16 fa-pencil"></span> <span>{lang}wcf.acp.article.add{/lang}</span></a></li>
	{/capture}
{/if}

{include file='header'}

{hascontent}
	<div class="paginationTop">
		{content}
			{pages print=true assign='pagesLinks' controller='CategoryArticleList' object=$category link="pageNo=%d"}
		{/content}
	</div>
{/hascontent}

{if $objects|count}
	<div class="section">
		{include file='articleListItems'}
	</div>
{else}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
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
