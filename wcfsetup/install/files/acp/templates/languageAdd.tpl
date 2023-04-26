{include file='header' pageTitle="wcf.acp.language.$action"}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.language.{@$action}{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='LanguageList'}{/link}" class="button">{icon name='list'} <span>{lang}wcf.acp.menu.link.language.list{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{@$form->getHtml()}

{include file='footer'}
