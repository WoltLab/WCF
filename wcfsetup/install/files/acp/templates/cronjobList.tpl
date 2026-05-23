{include file='header' pageTitle='wcf.acp.cronjob.list'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.cronjob.list{/lang}</h1>
		<p class="contentHeaderDescription">{lang}wcf.acp.cronjob.subtitle{/lang}</p>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='CronjobAdd'}{/link}" class="button buttonPrimary">{icon name='plus'} <span>{lang}wcf.acp.cronjob.add{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

<div class="section">
	{unsafe:$gridView->render()}
</div>

{include file='footer'}
