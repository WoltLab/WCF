{include file='header' pageTitle='wcf.acp.devtools.project.'|concat:$action}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.devtools.project.{$action}{/lang}{if $action == 'edit'}: {$formObject->name}{/if}</h1>
	</div>
	
	{hascontent}
		<nav class="contentHeaderNavigation">
			<ul>
				{content}
					{if $action === 'edit'}
						<li><a href="{link controller='DevtoolsProjectSync' id=$formObject->getObjectID()}{/link}" class="button">{icon name='arrows-rotate'} <span>{lang}wcf.acp.devtools.project.sync{/lang}</span></a></li>
						<li><a href="{link controller='DevtoolsProjectPipList' id=$formObject->getObjectID()}{/link}" class="button">{icon name='list'} <span>{lang}wcf.acp.devtools.project.pips{/lang}</span></a></li>
					{/if}
					{event name='contentHeaderNavigation'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</header>

{if $action === 'add'}
	<woltlab-core-notice type="info">{lang}wcf.acp.devtools.project.add.info{/lang}</woltlab-core-notice>
{elseif $action === 'edit'}
	{if $hasBrokenPath}
		<woltlab-core-notice type="error">{lang}wcf.acp.devtools.project.edit.error.brokenPath{/lang}</woltlab-core-notice>
	{else}
		<woltlab-core-notice type="warning">{lang}wcf.acp.devtools.project.edit.warning{/lang}</woltlab-core-notice>
	{/if}
	
	{if !$missingElements|empty}
		<woltlab-core-notice type="warning">{lang}wcf.acp.devtools.project.edit.warning.missingElements{/lang}</woltlab-core-notice>
	{/if}
{/if}

{unsafe:$form->getHtml()}

{include file='footer'}
