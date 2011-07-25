{include file='header'}

<header class="mainHeading">
	<img src="{@RELATIVE_WCF_DIR}icon/emailL.png" alt="" />
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
	<nav class="largeButtons">
		<ul>
			<li><a href="index.php?page=UserList{@SID_ARG_2ND}" title="{lang}wcf.acp.menu.link.user.list{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/usersM.png" alt="" /> <span>{lang}wcf.acp.menu.link.user.list{/lang}</span></a></li>
			<li><a href="index.php?form=UserSearch{@SID_ARG_2ND}" title="{lang}wcf.acp.user.search{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/searchM.png" alt="" /> <span>{lang}wcf.acp.user.search{/lang}</span></a></li>
			{if $additionalLargeButtons|isset}{@$additionalLargeButtons}{/if}
		</ul>
	</nav>
</div>

<form method="post" action="index.php?form=UserMail">
	<div class="border content">
		<div class="container-1">
			{if $action == ''}
				<fieldset>
					<legend>{lang}wcf.acp.user.sendMail.markedUsers{/lang}</legend>
					
					<div>
						{implode from=$users item=$user}<a href="index.php?form=UserEdit&amp;userID={@$user->userID}{@SID_ARG_2ND}">{$user}</a>{/implode}
					</div>
				</fieldset>	
			{/if}
			{if $action == 'group'}
				<fieldset>
					<legend>{lang}wcf.acp.user.sendMail.groups{/lang}</legend>
					
					<div class="formGroup{if $errorField == 'groupIDs'} formError{/if}">
						<div class="formGroupLabel">
							<label>{lang}wcf.acp.user.groups{/lang}</label>
						</div>
						<div class="formGroupField">
							<fieldset>
								<legend>{lang}wcf.acp.user.groups{/lang}</legend>
								
								<div class="formField">
									{htmlCheckboxes options=$groups name=groupIDs selected=$groupIDs}
								</div>
							</fieldset>
							{if $errorField == 'groupIDs'}
								<p class="innerError">
									{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								</p>
							{/if}
						</div>
					</div>
				</fieldset>	
			{/if}
			<fieldset>
				<legend>{lang}wcf.acp.user.sendMail.mail{/lang}</legend>
				
				<div>
					<div class="formElement{if $errorField == 'subject'} formError{/if}" id="subjectDiv">
						<div class="formFieldLabel">
							<label for="subject">{lang}wcf.acp.user.sendMail.subject{/lang}</label>
						</div>
						<div class="formField">
							<input type="text" class="inputText" id="subject" name="subject" value="{$subject}" />
							{if $errorField == 'subject'}
								<p class="innerError">
									{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								</p>
							{/if}
						</div>
						<div class="formFieldDesc hidden" id="subjectHelpMessage">
							<p>{lang}wcf.acp.user.sendMail.subject.description{/lang}</p>
						</div>
					</div>
					<script type="text/javascript">//<![CDATA[
						inlineHelp.register('subject');
					//]]></script>
					
					<div class="formElement{if $errorField == 'from'} formError{/if}" id="fromDiv">
						<div class="formFieldLabel">
							<label for="from">{lang}wcf.acp.user.sendMail.from{/lang}</label>
						</div>
						<div class="formField">
							<input type="text" class="inputText" id="from" name="from" value="{$from}" />
							{if $errorField == 'from'}
								<p class="innerError">
									{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								</p>
							{/if}
						</div>
						<div class="formFieldDesc hidden" id="fromHelpMessage">
							<p>{lang}wcf.acp.user.sendMail.from.description{/lang}</p>
						</div>
					</div>
					<script type="text/javascript">//<![CDATA[
						inlineHelp.register('from');
					//]]></script>
				
					<div class="formElement{if $errorField == 'text'} formError{/if}" id="textDiv">
						<div class="formFieldLabel">
							<label for="text">{lang}wcf.acp.user.sendMail.text{/lang}</label>
						</div>
						<div class="formField">
							<textarea id="text" name="text" rows="15" cols="40">{$text}</textarea>
							{if $errorField == 'text'}
								<p class="innerError">
									{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								</p>
							{/if}
						</div>
						<div class="formFieldDesc hidden" id="textHelpMessage">
							<p>{lang}wcf.acp.user.sendMail.text.description{/lang}</p>
						</div>
					</div>
					<script type="text/javascript">//<![CDATA[
						inlineHelp.register('text');
					//]]></script>
					
					<div class="formElement">
						<div class="formField">
							<label><input type="checkbox" name="enableHTML" id="enableHTML" value="1" {if $enableHTML == 1}checked="checked" {/if}/> {lang}wcf.acp.user.sendMail.enableHTML{/lang}</label>
						</div>
					</div>
				</div>
				
				{if $additionalFields|isset}{@$additionalFields}{/if}
			</fieldset>
		</div>
	</div>
	
	<div class="formSubmit">
		<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
		<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
		<input type="hidden" name="action" value="{@$action}" />
 		{@SID_INPUT_TAG}
 		<input type="hidden" name="userIDs" value="{@$userIDs}" />
 	</div>
</form>

{include file='footer'}
