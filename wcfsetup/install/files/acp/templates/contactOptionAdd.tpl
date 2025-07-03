{assign var='pageTitle' value='wcf.acp.contact.option.'|concat:$action}

{include file='header'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}{$pageTitle}{/lang}</h1>
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

{unsafe:$form->getHtml()}

{include file='footer'}
