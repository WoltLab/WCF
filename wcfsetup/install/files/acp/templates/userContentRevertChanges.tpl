{include file='header' pageTitle='wcf.acp.user.revertChanges'}

<header class="contentHeader">
	<h1 class="contentTitle">{lang}wcf.acp.user.revertChanges{/lang}</h1>
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

<form method="post" action="{link controller='UserContentRevertChanges'}{/link}">
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.acp.user.revertChanges.markedUsers{/lang}</h2>
		
		<div>
			{implode from=$users item='user'}<a href="{link controller='UserEdit' id=$user->userID}{/link}">{$user}</a>{/implode}
		</div>
		
		{event name='markedUserFields'}
	</section>
	
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.acp.user.revertChanges.timeframe{/lang}</h2>
		
		<dl{if $errorField == 'timeframe'} class="formError"{/if}>
			<dt><label for="timeframe">{lang}wcf.acp.user.revertChanges.timeframe{/lang}</label></dt>
			<dd>
				<input name="timeframe" type="number" min="0" value="{$timeframe}" />
				
				{if $errorField == 'timeframe'}
					<small class="innerError">
						{if $errorType == 'empty'}{lang}wcf.global.form.error.empty{/lang}{/if}
					</small>
				{/if}
				<small>{lang}wcf.acp.user.revertChanges.timeframe.description{/lang}</small>
			<dd>
		</dl>
		
		{event name='mergeFields'}
	</section>
	
	{event name='sections'}
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

{include file='footer'}
