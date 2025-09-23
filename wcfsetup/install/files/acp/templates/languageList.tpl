{include file='header' pageTitle='wcf.acp.language.list'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.language.list{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='LanguageAdd'}{/link}" class="button">{icon name='plus'} <span>{lang}wcf.acp.language.add{/lang}</span></a></li>
			<li><a href="{link controller='LanguageImport'}{/link}" class="button">{icon name='upload'} <span>{lang}wcf.acp.language.import{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

<div class="section">
	{unsafe:$gridView->render()}
</div>

{include file='footer'}
