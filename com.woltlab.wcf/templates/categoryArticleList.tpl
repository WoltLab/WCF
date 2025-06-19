{capture assign='pageTitle'}{$category->getTitle()}{/capture}

{capture assign='contentTitle'}{$category->getTitle()}{/capture}
{capture assign='contentDescription'}{if $category->descriptionUseHtml}{unsafe:$category->getDescription()}{else}{$category->getDescription()}{/if}{/capture}

{capture append='headContent'}
	{if $listView->getPageNo() < $listView->countPages()}
		<link rel="next" href="{link controller='CategoryArticleList' object=$category}pageNo={$listView->getPageNo() + 1}{/link}">
	{/if}
	{if $listView->getPageNo() > 1}
		<link rel="prev" href="{link controller='CategoryArticleList' object=$category}{if $listView->getPageNo() > 2}pageNo={$listView->getPageNo() - 1}{/if}{/link}">
	{/if}
	
	{if $__wcf->user->userID}
		<link rel="alternate" type="application/rss+xml" title="{lang}wcf.global.button.rss{/lang}" href="{link controller='ArticleRssFeed' id=$categoryID}at={$__wcf->user->userID}-{$__wcf->user->accessToken}{/link}">
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
	
	{if $__wcf->user->userID}
		<button type="button" class="markAllAsReadButton contentInteractionButton button small jsOnly">{icon name='check'} <span>{lang}wcf.global.button.markAllAsRead{/lang}</span></button>
	{/if}
{/capture}

{capture assign='contentInteractionDropdownItems'}
	{if $__wcf->user->userID}
		<li><a rel="alternate" href="{link controller='ArticleRssFeed' id=$categoryID}at={$__wcf->user->userID}-{$__wcf->user->accessToken}{/link}" class="rssFeed">{lang}wcf.global.button.rss{/lang}</a></li>
	{else}
		<li><a rel="alternate" href="{link controller='ArticleRssFeed' id=$categoryID}{/link}" class="rssFeed">{lang}wcf.global.button.rss{/lang}</a></li>
	{/if}
{/capture}

{include file='header'}

<div class="section entryCardList__container">
	{unsafe:$listView->render()}
</div>

{if $__wcf->user->userID}
	<script data-relocate="true">
		require(['WoltLabSuite/Core/Ui/Article/MarkAllAsRead'], ({ setup }) => {
			setup();
		});
	</script>
{/if}

{if $canManageArticles}
	{include file='shared_articleAddDialog'}
{/if}

{include file='footer'}
