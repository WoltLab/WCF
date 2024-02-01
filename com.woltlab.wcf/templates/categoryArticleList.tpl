{capture assign='pageTitle'}{$category->getTitle()}{/capture}

{capture assign='contentTitle'}{$category->getTitle()}{/capture}
{capture assign='contentDescription'}{if $category->descriptionUseHtml}{@$category->getDescription()}{else}{$category->getDescription()}{/if}{/capture}

{capture append='headContent'}
	{if $pageNo < $pages}
		<link rel="next" href="{link controller='CategoryArticleList' object=$category}pageNo={@$pageNo+1}{/link}">
	{/if}
	{if $pageNo > 1}
		<link rel="prev" href="{link controller='CategoryArticleList' object=$category}{if $pageNo > 2}pageNo={@$pageNo-1}{/if}{/link}">
	{/if}
	
	{if $__wcf->getUser()->userID}
		<link rel="alternate" type="application/rss+xml" title="{lang}wcf.global.button.rss{/lang}" href="{link controller='ArticleFeed' id=$categoryID}at={@$__wcf->getUser()->userID}-{@$__wcf->getUser()->accessToken}{/link}">
	{else}
		<link rel="alternate" type="application/rss+xml" title="{lang}wcf.global.button.rss{/lang}" href="{link controller='ArticleFeed' id=$categoryID}{/link}">
	{/if}
{/capture}

{if $__wcf->getSession()->getPermission('admin.content.article.canManageArticle')}
	{capture assign='contentHeaderNavigation'}
		{if $availableLanguages|count > 1}
			<li><a href="#" class="button buttonPrimary jsButtonArticleAdd">{icon name='plus'} <span>{lang}wcf.acp.article.add{/lang}</span></a></li>
		{else}
			<li><a href="{link controller='ArticleAdd'}categoryID={@$category->categoryID}{/link}" class="button buttonPrimary">{icon name='plus'} <span>{lang}wcf.acp.article.add{/lang}</span></a></li>
		{/if}
	{/capture}
{/if}

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

{capture assign='contentInteractionPagination'}
	{pages print=true assign='pagesLinks' controller='CategoryArticleList' object=$category link="pageNo=%d"}
{/capture}

{capture assign='contentInteractionButtons'}
	{include file='__userObjectWatchButton' isSubscribed=$category->isSubscribed() objectType='com.woltlab.wcf.article.category' objectID=$category->categoryID}
	
	<button type="button" class="markAllAsReadButton contentInteractionButton button small jsOnly">{icon name='check'} <span>{lang}wcf.global.button.markAllAsRead{/lang}</span></button>
{/capture}

{capture assign='contentInteractionDropdownItems'}
	<li><a rel="alternate" href="{if $__wcf->getUser()->userID}{link controller='ArticleFeed' id=$categoryID}at={@$__wcf->getUser()->userID}-{@$__wcf->getUser()->accessToken}{/link}{else}{link controller='ArticleFeed' id=$categoryID}{/link}{/if}" class="rssFeed">{lang}wcf.global.button.rss{/lang}</a></li>
{/capture}

{include file='header'}

{if $objects|count}
	<div class="section">
		{include file='articleListItems'}
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

<script data-relocate="true">
	require(['WoltLabSuite/Core/Ui/Article/MarkAllAsRead'], ({ setup }) => {
		setup();
	});
</script>

{include file='shared_articleAddDialog'}

{include file='footer'}
