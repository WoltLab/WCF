{include file='header' pageTitle='wcf.acp.user.merge'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.user.merge{/lang}</h1>
	</div>
	
	{hascontent}
		<nav class="contentHeaderNavigation">
			<ul>
				{content}{event name='contentHeaderNavigation'}{/content}
			</ul>
		</nav>
	{/hascontent}
</header>

{include file='shared_formError'}

<woltlab-core-notice type="warning">{lang}wcf.acp.user.merge.warning{/lang}</woltlab-core-notice>

<form method="post" action="{link controller='UserMerge'}{/link}">
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.acp.user.merge.markedUsers{/lang}</h2>
		
		<div>
			{implode from=$users item='user'}<a href="{link controller='UserEdit' id=$user->userID}{/link}">{$user}</a>{/implode}
		</div>
		
		{event name='markedUserFields'}
	</section>
	
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.acp.user.merge.destination{/lang}</h2>
		
		<dl{if $errorField == 'destinationUserID'} class="formError"{/if}>
			<dt><label for="destinationUserID">{lang}wcf.acp.user.merge.destination{/lang}</label></dt>
			<dd>
				<select name="destinationUserID" id="destinationUserID">
					<option value="0">{lang}wcf.global.noSelection{/lang}</option>
					{foreach from=$users item=user}
						<option value="{$user->userID}">{$user->username}</option>
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
	</section>
	
	{event name='sections'}
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		{csrfToken}
	</div>
</form>

{include file='footer'}
