{include file='header' pageTitle='wcf.acp.article.list'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.article.list{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			{if $availableLanguages|count > 1}
				<li><a href="#" class="button jsButtonArticleAdd">{icon name='plus'} <span>{lang}wcf.acp.article.add{/lang}</span></a></li>
			{else}
				<li><a href="{link controller='ArticleAdd'}{/link}" class="button">{icon name='plus'} <span>{lang}wcf.acp.article.add{/lang}</span></a></li>
			{/if}
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

<div class="section">
	{unsafe:$gridView->render()}
</div>

{include file='shared_articleAddDialog' categoryID=0}

{include file='footer'}
