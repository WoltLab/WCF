{if $action == 'all'}
	{assign var='pageTitle' value='wcf.acp.user.sendMail.all'}
{elseif $action == 'group'}
	{assign var='pageTitle' value='wcf.acp.user.sendMail.group'}
{else}
	{assign var='pageTitle' value='wcf.acp.user.sendMail'}
{/if}

{include file='header'}

{if $mailID|isset}
	<script>
		//<![CDATA[
		$(function() {
			new WCF.ACP.Worker('mail', 'wcf\\system\\worker\\MailWorker', '', {
				mailID: {@$mailID}
			});
		});
		//]]>
	</script>
{/if}

<header class="boxHeadline">
	<h1>{lang}{@$pageTitle}{/lang}</h1>
</header>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

<div class="contentNavigation">
	<nav>
		<ul>
			<li><a href="{link controller='UserList'}{/link}" class="button"><span class="icon icon16 icon-list"></span> <span>{lang}wcf.acp.menu.link.user.list{/lang}</span></a></li>
			<li><a href="{link controller='UserSearch'}{/link}" class="button"><span class="icon icon16 icon-search"></span> <span>{lang}wcf.acp.user.search{/lang}</span></a></li>
			
			{event name='contentNavigationButtons'}
		</ul>
	</nav>
</div>

<form method="post" action="{link controller='UserMail'}{/link}">
	<div class="container containerPadding marginTop">
		{if $action == ''}
			<fieldset>
				<legend>{lang}wcf.acp.user.sendMail.markedUsers{/lang}</legend>
				
				<div>
					{implode from=$userList item=$user}<a href="{link controller='UserEdit' id=$user->userID}{/link}">{$user}</a>{/implode}
				</div>
				
				{event name='markedUserFields'}
			</fieldset>
		{/if}
		
		{if $action == 'group'}
			<fieldset>
				<legend>{lang}wcf.acp.user.sendMail.groups{/lang}</legend>
				
				<dl{if $errorField == 'groupIDs'} class="formError"{/if}>
					<dd>
						{htmlCheckboxes options=$groups name=groupIDs selected=$groupIDs}
						{if $errorField == 'groupIDs'}
							<small class="innerError">
								{if $errorType == 'empty'}
									{lang}wcf.global.form.error.empty{/lang}
								{else}
									{lang}wcf.acp.user.sendMail.groups.error.{@$errorType}{/lang}
								{/if}
							</small>
						{/if}
					</dd>
				</dl>
				
				{event name='userGroupFields'}
			</fieldset>
		{/if}
		
		<fieldset>
			<legend>{lang}wcf.acp.user.sendMail.mail{/lang}</legend>
			
			<dl{if $errorField == 'subject'} class="formError"{/if}>
				<dt><label for="subject">{lang}wcf.acp.user.sendMail.subject{/lang}</label></dt>
				<dd>
					<input type="text" id="subject" name="subject" value="{$subject}" autofocus="autofocus" class="long" />
					{if $errorField == 'subject'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.acp.user.sendMail.subject.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>
			
			<dl{if $errorField == 'from'} class="formError"{/if}>
				<dt><label for="from">{lang}wcf.acp.user.sendMail.from{/lang}</label></dt>
				<dd>
					<input type="text" id="from" name="from" value="{$from}" class="long" />
					{if $errorField == 'from'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.acp.user.sendMail.subject.from.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
					<small>{lang}wcf.acp.user.sendMail.from.description{/lang}</small>
				</dd>
			</dl>
			
			<dl{if $errorField == 'text'} class="formError"{/if}>
				<dt><label for="text">{lang}wcf.acp.user.sendMail.text{/lang}</label></dt>
				<dd>
					<textarea id="text" name="text" rows="15" cols="40" class="long">{$text}</textarea>
					{if $errorField == 'text'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.acp.user.sendMail.text.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>
			
			<dl>
				<dt></dt>
				<dd>
					<label><input type="checkbox" id="enableHTML" name="enableHTML" {if $enableHTML == 1}checked="checked" {/if}value="1" /> {lang}wcf.acp.user.sendMail.enableHTML{/lang}</label>
				</dd>
			</dl>
			
			{event name='mailFields'}
		</fieldset>
		
		{event name='fieldsets'}
	</div>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		<input type="hidden" name="action" value="{@$action}" />
		<input type="hidden" name="userIDs" value="{implode from=$userIDs item=userID glue=','}{@$userID}{/implode}" />
	</div>
</form>

{include file='footer'}
