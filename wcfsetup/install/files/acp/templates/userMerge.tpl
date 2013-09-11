{include file='header' pageTitle='wcf.acp.user.merge'}

<header class="boxHeadline">
	<h1>{lang}wcf.acp.user.merge{/lang}</h1>
</header>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

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

<form method="post" action="{link controller='UserMerge'}{/link}">
	<div class="container containerPadding marginTop">
		<fieldset>
			<legend>{lang}wcf.acp.user.merge.markedUsers{/lang}</legend>
			
			<div>
				{implode from=$users item='user'}<a href="{link controller='UserEdit' id=$user->userID}{/link}">{$user}</a>{/implode}
			</div>
			
			{event name='markedUserFields'}
		</fieldset>
		
		<fieldset>
			<legend>{lang}wcf.acp.user.merge.destination{/lang}</legend>
			
			<dl{if $errorField == 'destinationUserID'} class="formError"{/if}>
				<dt><label for="destinationUserID">{lang}wcf.acp.user.merge.destination{/lang}</label></dt>
				<dd>
					<select name="destinationUserID" id="destinationUserID">
						<option value="0">{lang}wcf.global.noSelection{/lang}</option>
						{foreach from=$users item=user}
							<option value="{@$user->userID}">{$user->username}</option>
						{/foreach}
					</select>
					
					{if $errorField == 'destinationUserID'}
						<small class="innerError">
							{if $errorType == 'empty'}{lang}wcf.global.form.error.empty{/lang}{/if}
						</small>
					{/if}
					<small>{lang}wcf.acp.user.merge.destination.description{/lang}</small>
				<dd>
			</dl>
			
			{event name='mergeFields'}
		</fieldset>
		
		{event name='fieldsets'}
	</div>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
	</div>
</form>

{include file='footer'}
