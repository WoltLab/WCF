{capture assign='contentTitle'}{lang}wcf.article.userArticles{/lang}{/capture}
{capture assign='pageTitle'}{lang}wcf.article.userArticles{/lang}{/capture}

{include file='header'}

<div class="section {$listView->getContainerCssClassName()}">
	{unsafe:$listView->render()}
</div>

{include file='footer'}
