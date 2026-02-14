{include file='header' pageTitle='wcf.acp.menu.link.userTrophy.list'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.menu.link.userTrophy.list{/lang}</h1>
	</div>

	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='UserTrophyAdd'}{/link}" class="button">{icon name='plus'} <span>{lang}wcf.acp.menu.link.userTrophy.add{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

<div class="section">
	{unsafe:$gridView->render()}
</div>

{include file='footer'}
