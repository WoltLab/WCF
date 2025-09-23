{include file='header' pageTitle='wcf.acp.style.list'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.style.list{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='StyleAdd'}{/link}" class="button">{icon name='plus'} <span>{lang}wcf.acp.menu.link.style.add{/lang}</span></a></li>
			<li><a href="{link controller='StyleImport'}{/link}" class="button">{icon name='upload'} <span>{lang}wcf.acp.menu.link.style.import{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

<div class="section">
	{unsafe:$gridView->render()}
</div>

{include file='footer'}
