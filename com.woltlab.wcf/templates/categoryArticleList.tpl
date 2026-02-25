{capture assign='pageTitle'}{$category->getTitle()}{/capture}

{capture assign='contentTitle'}{$category->getTitle()}{/capture}
{capture assign='contentDescription'}{if $category->descriptionUseHtml}{unsafe:$category->getDescription()}{else}{$category->getDescription()}{/if}{/capture}

{capture append='headContent'}
	{if $__wcf->user->userID}
		<link rel="alternate" type="application/rss+xml" title="{lang}wcf.global.button.rss{/lang}" href="{link controller='ArticleRssFeed' id=$categoryID at=$__wcf->user->getAccessToken()}{/link}">
	{else}
		<link rel="alternate" type="application/rss+xml" title="{lang}wcf.global.button.rss{/lang}" href="{link controller='ArticleRssFeed' id=$categoryID}{/link}">
	{/if}
{/capture}

{capture assign='contentHeaderNavigation'}
	{if $canManageArticles}
		{if $availableLanguages|count > 1}
			<li><button type="button" class="button buttonPrimary jsButtonArticleAdd">{icon name='plus'} <span>{lang}wcf.acp.article.add{/lang}</span></button></li>
		{else}
			<li><a href="{link controller='ArticleAdd'}categoryID={$category->categoryID}{/link}" class="button buttonPrimary">{icon name='plus'} <span>{lang}wcf.acp.article.add{/lang}</span></a></li>
		{/if}
	{/if}
{/capture}

{capture assign='contentInteractionButtons'}
	{include file='__userObjectWatchButton' isSubscribed=$category->isSubscribed() objectType='com.woltlab.wcf.article.category' objectID=$category->categoryID}
	
	{if $listView->canMarkAsRead()}
		<button type="button" class="markAllArticlesAsReadButton contentInteractionButton button small jsOnly">
			{icon name='check'}
			<span>{lang}wcf.global.button.markAllAsRead{/lang}</span>
		</button>

		<script data-relocate="true">
			require(['WoltLabSuite/Core/Component/Article/MarkAllArticlesAsRead'], ({ setup }) => {
				setup(
					document.querySelector('.markAllArticlesAsReadButton'),
					document.getElementById('{unsafe:$listView->getID()|encodeJS}_items')
				);
			});
		</script>
	{/if}
{/capture}

{capture assign='contentInteractionDropdownItems'}
	{if $__wcf->user->userID}
		<li><a rel="alternate" href="{link controller='ArticleRssFeed' id=$categoryID at=$__wcf->user->getAccessToken()}{/link}" class="rssFeed">{lang}wcf.global.button.rss{/lang}</a></li>
	{else}
		<li><a rel="alternate" href="{link controller='ArticleRssFeed' id=$categoryID}{/link}" class="rssFeed">{lang}wcf.global.button.rss{/lang}</a></li>
	{/if}
{/capture}

{include file='header'}

<div class="section {$listView->getContainerCssClassName()}">
	{unsafe:$listView->render()}
</div>

{if $canManageArticles}
	{include file='shared_articleAddDialog'}
{/if}

{include file='footer'}
