{include file='header' pageTitle='wcf.acp.paidSubscription.user.'|concat:$action}

{if $action == 'add'}
	<script data-relocate="true">
		require(['WoltLabSuite/Core/Ui/User/Search/Input'], (UiUserSearchInput) => {
			new UiUserSearchInput(document.getElementById('username'));
		});
	</script>
{/if}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.paidSubscription.user.{$action}{/lang}</h1>
		{if $action == 'add'}
			<p class="contentHeaderDescription">{$subscription->getTitle()}</p>
		{else}
			<p class="contentHeaderDescription">{$subscriptionUser->getUser()->username}</p>
		{/if}
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

{include file='shared_formNotice'}

<form method="post" action="{if $action == 'add'}{link controller='PaidSubscriptionUserAdd' id=$subscriptionID}{/link}{else}{link controller='PaidSubscriptionUserEdit' id=$subscriptionUserID}{/link}{/if}">
	<div class="section">
		{if $action == 'add'}
			<dl{if $errorField == 'username'} class="formError"{/if}>
				<dt><label for="username">{lang}wcf.user.username{/lang}</label></dt>
				<dd>
					<input type="text" id="username" name="username" value="{$username}" autofocus class="medium">
					{if $errorField == 'username'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.user.username.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>
		{/if}
		
		{if $subscription->subscriptionLength}
			<dl{if $errorField == 'endDate'} class="formError"{/if}>
				<dt><label for="endDate">{lang}wcf.acp.paidSubscription.user.endDate{/lang}</label></dt>
				<dd>
					<input type="date" id="endDate" name="endDate" value="{$endDate}" class="medium" data-ignore-timezone="true">
					{if $errorField == 'endDate'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.acp.paidSubscription.user.endDate.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>
		{/if}
	</div>
	
	{event name='sections'}
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		{csrfToken}
	</div>
</form>

{include file='footer'}
