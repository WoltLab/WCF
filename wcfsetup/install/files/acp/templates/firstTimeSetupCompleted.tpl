{include file='header' pageTitle='wcf.acp.firstTimeSetup.completed'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.firstTimeSetup.completed{/lang}</h1>
	</div>
	
	{hascontent}
		<nav class="contentHeaderNavigation">
			<ul>
				{content}
					<li><a href="{link}{/link}" class="button">{icon name='house'} <span>{lang}wcf.global.acp{/lang}</span></a></li>
					
					{event name='contentHeaderNavigation'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</header>

{include file='footer'}
