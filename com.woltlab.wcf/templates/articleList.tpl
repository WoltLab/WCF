{capture assign='headContent'}
	{if $pageNo < $pages}
		<link rel="next" href="{link controller='ArticleList'}pageNo={@$pageNo+1}{/link}">
	{/if}
	{if $pageNo > 1}
		<link rel="prev" href="{link controller='ArticleList'}{if $pageNo > 2}pageNo={@$pageNo-1}{/if}{/link}">
	{/if}
	
	{if $__wcf->getUser()->userID}
		<link rel="alternate" type="application/rss+xml" title="{lang}wcf.global.button.rss{/lang}" href="{link controller='ArticleFeed'}at={@$__wcf->getUser()->userID}-{@$__wcf->getUser()->accessToken}{/link}">
	{else}
		<link rel="alternate" type="application/rss+xml" title="{lang}wcf.global.button.rss{/lang}" href="{link controller='ArticleFeed'}{/link}">
	{/if}
{/capture}

{capture assign='contentHeaderNavigation'}
	{if $__wcf->getSession()->getPermission('admin.content.article.canManageArticle') || $__wcf->getSession()->getPermission('admin.content.article.canContributeArticle')}
		{if $availableLanguages|count > 1}
			<li><a href="#" class="button buttonPrimary jsButtonArticleAdd"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.article.add{/lang}</span></a></li>
		{else}
			<li><a href="{link controller='ArticleAdd'}{/link}" class="button buttonPrimary"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.article.add{/lang}</span></a></li>
		{/if}
	{/if}
{/capture}

{capture assign='sidebarRight'}
	{if !$labelGroups|empty}
		<form id="sidebarForm" method="post" action="{link application='wcf' controller=$controllerName object=$controllerObject}{/link}">
			<section class="box">
				<h2 class="boxTitle">{lang}wcf.label.label{/lang}</h2>
				
				<div class="boxContent">
					<dl>
						{include file='__labelSelection'}
					</dl>
					<div class="formSubmit">
						<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
					</div>
				</div>
			</section>
		</form>
		
		<script data-relocate="true">
			$(function() {
				WCF.Language.addObject({
					'wcf.label.none': '{jslang}wcf.label.none{/jslang}',
					'wcf.label.withoutSelection': '{jslang}wcf.label.withoutSelection{/jslang}'
				});
				
				new WCF.Label.Chooser({ {implode from=$labelIDs key=groupID item=labelID}{@$groupID}: {@$labelID}{/implode} }, '#sidebarForm', undefined, true);
			});
		</script>
	{/if}
{/capture}

{assign var='additionalLinkParameters' value=''}
{if $user}{capture append='additionalLinkParameters'}&userID={@$user->userID}{/capture}{/if}
{if $labelIDs|count}{capture append='additionalLinkParameters'}{foreach from=$labelIDs key=labelGroupID item=labelID}&labelIDs[{@$labelGroupID}]={@$labelID}{/foreach}{/capture}{/if}

{capture assign='contentInteractionPagination'}
	{pages print=true assign='pagesLinks' controller='ArticleList' link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder$additionalLinkParameters"}
{/capture}

{capture assign='contentInteractionButtons'}
	<div class="contentInteractionButton dropdown jsOnly">
		<a href="#" class="button small dropdownToggle"><span class="icon icon16 fa-sort-amount-desc"></span> <span>{lang}wcf.article.button.sort{/lang}</span></a>
		<ul class="dropdownMenu">
			<li><a href="{link controller='ArticleList'}pageNo={@$pageNo}{if $user}&userID={@$user->userID}{/if}&sortField=title&sortOrder={if $sortField == 'title' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.title{/lang}{if $sortField == 'title'} <span class="icon icon16 fa-caret-{if $sortOrder == 'ASC'}up{else}down{/if}"></span>{/if}</a></li>
			<li><a href="{link controller='ArticleList'}pageNo={@$pageNo}{if $user}&userID={@$user->userID}{/if}&sortField=time&sortOrder={if $sortField == 'time' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.date{/lang}{if $sortField == 'time'} <span class="icon icon16 fa-caret-{if $sortOrder == 'ASC'}up{else}down{/if}"></span>{/if}</a></li>
			
			{event name='sortOptions'}
		</ul>
	</div>
	{if ARTICLE_ENABLE_VISIT_TRACKING}
		<a href="#" class="markAllAsReadButton contentInteractionButton button small jsOnly"><span class="icon icon16 fa-check"></span> <span>{lang}wcf.global.button.markAllAsRead{/lang}</span></a>
	{/if}
{/capture}

{capture assign='contentInteractionDropdownItems'}
	<li><a rel="alternate" href="{if $__wcf->getUser()->userID}{link controller='ArticleFeed'}at={@$__wcf->getUser()->userID}-{@$__wcf->getUser()->accessToken}{/link}{else}{link controller='ArticleFeed'}{/link}{/if}" class="rssFeed">{lang}wcf.global.button.rss{/lang}</a></li>
{/capture}

{include file='header'}

{if $objects|count}
	<div class="section">
		{include file='articleListItems'}
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

{if ARTICLE_ENABLE_VISIT_TRACKING}
	<script data-relocate="true">
		require(['WoltLabSuite/Core/Ui/Article/MarkAllAsRead'], function(UiArticleMarkAllAsRead) {
			UiArticleMarkAllAsRead.init();
		});
	</script>
{/if}

{if $__wcf->getSession()->getPermission('admin.content.article.canManageArticle') || $__wcf->getSession()->getPermission('admin.content.article.canContributeArticle')}
	{include file='articleAddDialog'}
{/if}

{include file='footer'}
