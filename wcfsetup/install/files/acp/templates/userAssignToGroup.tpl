{include file='header' pageTitle='wcf.acp.user.assignToGroup'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.user.assignToGroup{/lang}</h1>
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

<form method="post" action="{link controller='UserAssignToGroup'}{/link}">
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.acp.user.assignToGroup.markedUsers{/lang}</h2>
		
		<div>
			{implode from=$users item=$user}<a href="{link controller='UserEdit' id=$user->userID}{/link}">{$user}</a>{/implode}
		</div>
		
		{event name='markedUserFields'}
	</section>
	
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.acp.user.groups{/lang}</h2>
		
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
	</section>
	
	{event name='sections'}
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		{csrfToken}
	</div>
</form>

{include file='footer'}
