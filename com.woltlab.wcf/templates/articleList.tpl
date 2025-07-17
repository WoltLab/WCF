{capture append='headContent'}
	{if $listView->getPageNo() < $listView->countPages()}
		<link rel="next" href="{link controller='ArticleList'}pageNo={$listView->getPageNo() + 1}{/link}">
	{/if}
	{if $listView->getPageNo() > 1}
		<link rel="prev" href="{link controller='ArticleList'}{if $listView->getPageNo() > 2}pageNo={$listView->getPageNo() - 1}{/if}{/link}">
	{/if}
	
	{if $__wcf->user->userID}
		<link rel="alternate" type="application/rss+xml" title="{lang}wcf.global.button.rss{/lang}" href="{link controller='ArticleRssFeed' at=$__wcf->user->getAccessToken()}{/link}">
	{else}
		<link rel="alternate" type="application/rss+xml" title="{lang}wcf.global.button.rss{/lang}" href="{link controller='ArticleRssFeed'}{/link}">
	{/if}
{/capture}

{capture assign='contentHeaderNavigation'}
	{if $canManageArticles}
		{if $availableLanguages|count > 1}
			<li><button type="button" class="button buttonPrimary jsButtonArticleAdd">{icon name='plus'} <span>{lang}wcf.acp.article.add{/lang}</span></button></li>
		{else}
			<li><a href="{link controller='ArticleAdd'}{/link}" class="button buttonPrimary">{icon name='plus'} <span>{lang}wcf.acp.article.add{/lang}</span></a></li>
		{/if}
	{/if}
{/capture}

{capture assign='contentInteractionButtons'}
	{if $__wcf->user->userID}
		<button type="button" class="markAllAsReadButton contentInteractionButton button small jsOnly">{icon name='check'} <span>{lang}wcf.global.button.markAllAsRead{/lang}</span></button>
	{/if}
{/capture}

{capture assign='contentInteractionDropdownItems'}
	{if $__wcf->user->userID}
		<li><a rel="alternate" href="{link controller='ArticleRssFeed' at=$__wcf->user->getAccessToken()}{/link}" class="rssFeed">{lang}wcf.global.button.rss{/lang}</a></li>
	{else}
		<li><a rel="alternate" href="{link controller='ArticleRssFeed'}{/link}" class="rssFeed">{lang}wcf.global.button.rss{/lang}</a></li>
	{/if}
{/capture}

{include file='header'}

<div class="section {$listView->getContainerCssClassName()}">
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
