{include file='header' pageTitle='wcf.acp.paidSubscription.user.'|concat:$action}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.paidSubscription.user.{$action}{/lang}{if $action == 'edit'}: {$formObject->getUser()->username}{/if}</h1>
		<p class="contentHeaderDescription">{$subscription->getTitle()}</p>
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

{unsafe:$form->getHtml()}

{include file='footer'}
