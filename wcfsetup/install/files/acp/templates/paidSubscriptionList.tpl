{include file='header' pageTitle='wcf.acp.paidSubscription.list'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.paidSubscription.list{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='PaidSubscriptionAdd'}{/link}" class="button">{icon name='plus'} <span>{lang}wcf.acp.paidSubscription.add{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

<div class="section">
	{unsafe:$gridView->render()}
</div>

{include file='footer'}
