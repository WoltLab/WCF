{include file='header' pageTitle='wcf.acp.cronjob.'|concat:$action}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.cronjob.{$action}{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			{if $action == 'edit'}
				<li>
					{unsafe:$interactionContextMenu->render()}
				</li>
			{/if}
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

<woltlab-core-notice type="info">{lang}wcf.acp.cronjob.intro{/lang}</woltlab-core-notice>

{unsafe:$form->getHtml()}

{include file='footer'}
