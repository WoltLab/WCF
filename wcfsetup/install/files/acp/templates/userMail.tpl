{include file='header'}

{if $mailID|isset}
	<script type="text/javascript">
		//<![CDATA[
		$(function() {
			new WCF.ACP.Worker('mail', 'wcf\\system\\worker\\MailWorker', {
				mailID: {@$mailID}
			});
		});
		//]]>
	</script>
{/if}

<header class="mainHeading">
	<img src="{@RELATIVE_WCF_DIR}icon/email1.svg" alt="" />
	<hgroup>
		<h1>
			{if $action == 'all'}
				{lang}wcf.acp.user.sendMail.all{/lang}
			{elseif $action == 'group'}
				{lang}wcf.acp.user.sendMail.group{/lang}
			{else}
				{lang}wcf.acp.user.sendMail{/lang}
			{/if}
		</h1>
	</hgroup>
</header>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

<div class="contentHeader">
	<nav>
		<ul class="largeButtons">
			<li><a href="{link controller='UserList'}{/link}" title="{lang}wcf.acp.menu.link.user.list{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/users1.svg" alt="" /> <span>{lang}wcf.acp.menu.link.user.list{/lang}</span></a></li>
			<li><a href="{link controller='UserSearch'}{/link}" title="{lang}wcf.acp.user.search{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/search1.svg" alt="" /> <span>{lang}wcf.acp.user.search{/lang}</span></a></li>
			
			{event name='largeButtons'}
		</ul>
	</nav>
</div>

<form method="post" action="{link controller='UserMail'}{/link}">
	<div class="border content">
		
		{if $action == ''}
			<fieldset>
				<legend>{lang}wcf.acp.user.sendMail.markedUsers{/lang}</legend>
				
				<div>
					{implode from=$users item=$user}<a href="{link controller='UserEdit' id=$user->userID}{/link}">{$user}</a>{/implode}
				</div>
			</fieldset>	
		{/if}
		
		{if $action == 'group'}
			<fieldset>
				<legend>{lang}wcf.acp.user.sendMail.groups{/lang}</legend>
				
				<dl{if $errorField == 'groupIDs'} class="formError"{/if}>
					<dt>
						<label>{lang}wcf.acp.user.groups{/lang}</label>
					</dt>
					<dd>
						<fieldset>
							<legend>{lang}wcf.acp.user.groups{/lang}</legend>
							
							<dl>
								<dd>
									{htmlCheckboxes options=$groups name=groupIDs selected=$groupIDs}
								</dd>
							</dl>
						</fieldset>
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
			</fieldset>	
		{/if}
		
		<fieldset>
			<legend>{lang}wcf.acp.user.sendMail.mail{/lang}</legend>
			
			<dl{if $errorField == 'subject'} class="formError"{/if}>
				<dt><label for="subject">{lang}wcf.acp.user.sendMail.subject{/lang}</label></dt>
				<dd>
					<input type="text" id="subject" name="subject" value="{$subject}" class="long" />
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
					<input type="text" id="from" name="from" value="{$from}" class="medium" />
					{if $errorField == 'from'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.acp.user.sendMail.subject.from.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
					<small>{lang}wcf.acp.user.sendMail.from.description{/lang}</small><!-- ToDo: Language variable contains paragraphs! -->
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
		<input type="reset" value="{lang}wcf.global.button.reset{/lang}" accesskey="r" />
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		<input type="hidden" name="action" value="{@$action}" />
 		{@SID_INPUT_TAG}
 		<input type="hidden" name="userIDs" value="{@$userIDs}" />
 	</div>
</form>

{include file='footer'}
