{include file='header' pageTitle='wcf.acp.group.assignment.list'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.group.assignment.list{/lang} <span class="badge badgeInverse">{#$gridView->countRows()}</span></h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='UserGroupAssignmentAdd'}{/link}" class="button">{icon name='plus'} <span>{lang}wcf.acp.group.assignment.button.add{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{if $hasLegacyObjects}
	<woltlab-core-notice type="warning">
		{lang}wcf.acp.group.assignment.legacyNotice{/lang}
	</woltlab-core-notice>
{/if}

<div class="section">
	{unsafe:$gridView->render()}
</div>

{include file='footer'}
