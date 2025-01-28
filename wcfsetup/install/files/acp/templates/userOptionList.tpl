{include file='header' pageTitle='wcf.acp.user.option.list'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.user.option.list{/lang} <span class="badge badgeInverse">{#$gridView->countRows()}</span></h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='UserOptionAdd'}{/link}" class="button">{icon name='plus'} <span>{lang}wcf.acp.user.option.add{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

<div class="section">
	{unsafe:$gridView->render()}
</div>

{include file='footer'}
