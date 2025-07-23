{if $searchID}
	{capture assign='pageTitle'}{lang}wcf.user.search.results{/lang}{/capture}
	{capture assign='contentTitle'}{lang}wcf.user.search.results{/lang}{/capture}
{/if}

{capture assign='headContent'}
	{if $listView->getPageNo() < $listView->countPages()}
		<link rel="next" href="{link controller='MembersList'}pageNo={$listView->getPageNo() + 1}{/link}">
	{/if}
	{if $listView->getPageNo() > 1}
		<link rel="prev" href="{link controller='MembersList'}{if $listView->getPageNo() > 2}pageNo={$listView->getPageNo() - 1}{/if}{/link}">
	{/if}
{/capture}

{capture assign='contentInteractionButtons'}
	<a href="{link controller='UserSearch'}{/link}" class="contentInteractionButton button small">{icon name='search'} <span>{lang}wcf.user.search{/lang}</span></a>
{/capture}

{include file='header'}

<div class="section {$listView->getContainerCssClassName()}">
	{unsafe:$listView->render()}
</div>

{include file='footer'}
