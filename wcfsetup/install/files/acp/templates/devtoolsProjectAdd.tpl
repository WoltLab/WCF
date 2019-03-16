{include file='header' pageTitle='wcf.acp.devtools.project.'|concat:$action}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.devtools.project.{$action}{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='DevtoolsProjectList'}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}wcf.acp.menu.link.devtools.project.list{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{if $action === 'add'}
	<p class="info">{lang}wcf.acp.devtools.project.add.info{/lang}</p>
{elseif $action === 'edit'}
	<p class="warning">{lang}wcf.acp.devtools.project.edit.warning{/lang}</p>
{/if}

{@$form->getHtml()}

{include file='footer'}
