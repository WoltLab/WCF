{include file='header' pageTitle='wcf.acp.sessionLog.list'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.sessionLog.list{/lang} <span class="badge badgeInverse">{#$gridView->countRows()}</span></h1>
	</div>
	
	{hascontent}
		<nav class="contentHeaderNavigation">
			<ul>
				{content}{event name='contentHeaderNavigation'}{/content}
			</ul>
		</nav>
	{/hascontent}
</header>

<div class="section">
	{unsafe:$gridView->render()}
</div>

{include file='footer'}
