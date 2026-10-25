{include file='header' pageTitle='wcf.acp.paidSubscription.user.'|concat:$action}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.paidSubscription.user.{$action}{/lang}{if $action == 'edit'}: {$formObject->getUser()->username}{/if}</h1>
		<p class="contentHeaderDescription">{$subscription->getTitle()}</p>
	</div>

	<nav class="contentHeaderNavigation">
		<ul>
			{if $action == 'add'}
				<li><a href="{link controller='PaidSubscriptionList'}{/link}" class="button">{icon name='list'} <span>{lang}wcf.acp.menu.link.paidSubscription.list{/lang}</span></a></li>
			{else}
				<li><a href="{link controller='PaidSubscriptionUserList'}{/link}" class="button">{icon name='list'} <span>{lang}wcf.acp.menu.link.paidSubscription.user.list{/lang}</span></a></li>
			{/if}

			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{unsafe:$form->getHtml()}

{include file='footer'}
