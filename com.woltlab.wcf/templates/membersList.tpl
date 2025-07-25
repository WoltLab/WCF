{if $searchID}
	{capture assign='pageTitle'}{lang}wcf.user.search.results{/lang}{/capture}
	{capture assign='contentTitle'}{lang}wcf.user.search.results{/lang}{/capture}
{/if}

{capture assign='contentInteractionButtons'}
	<a href="{link controller='UserSearch'}{/link}" class="contentInteractionButton button small">{icon name='search'} <span>{lang}wcf.user.search{/lang}</span></a>
{/capture}

{include file='header'}

<div class="section {$listView->getContainerCssClassName()}">
	{unsafe:$listView->render()}
</div>

{include file='footer'}
