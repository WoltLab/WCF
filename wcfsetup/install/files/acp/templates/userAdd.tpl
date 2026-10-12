{include file='header' pageTitle='wcf.acp.user.'|concat:$action}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.user.{$action}{/lang}</h1>
	</div>

	{hascontent}
		<nav class="contentHeaderNavigation">
			<ul>
				{content}
					{event name='contentHeaderNavigation'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</header>

{if $action === 'edit'}
	{unsafe:$userProfileHeaderView}

	{if $userID == $__wcf->user->userID}
		<woltlab-core-notice type="warning">{lang}wcf.acp.user.edit.warning.selfEdit{/lang}</woltlab-core-notice>
	{/if}
{/if}

{unsafe:$form->getHtml()}

{include file='footer'}
