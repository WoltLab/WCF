{include file='header' pageTitle='wcf.acp.user.assignToGroup'}

<header class="boxHeadline">
	<h1>{lang}wcf.acp.user.assignToGroup{/lang}</h1>
</header>

{include file='formError'}

<div class="contentNavigation">
	{hascontent}
		<nav>
			<ul>
				{content}
					{event name='contentNavigationButtons'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</div>

<form method="post" action="{link controller='UserAssignToGroup'}{/link}">
	<div class="container containerPadding marginTop">
		<fieldset>
			<legend>{lang}wcf.acp.user.assignToGroup.markedUsers{/lang}</legend>
			
			<div>
				{implode from=$users item=$user}<a href="{link controller='UserEdit' id=$user->userID}{/link}">{$user}</a>{/implode}
			</div>
			
			{event name='markedUserFields'}
		</fieldset>
		
		<fieldset>
			<legend>{lang}wcf.acp.user.groups{/lang}</legend>
			
			<dl{if $errorField == 'groupIDs'} class="formError"{/if}>
				<dd>
					{htmlCheckboxes options=$groups name=groupIDs selected=$groupIDs}
					{if $errorField == 'groupIDs'}
						<small class="innerError">
							{if $errorType == 'empty'}{lang}wcf.global.form.error.empty{/lang}{/if}
						</small>
					{/if}
				<dd>
			</dl>
			
			{event name='userGroupFields'}
		</fieldset>
		
		{event name='fieldsets'}
	</div>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

{include file='footer'}
