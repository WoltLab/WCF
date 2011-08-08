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
									{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								</small>
							{/if}
						</dd>
					</dl>
				</fieldset>	
			{/if}
			<fieldset>
				<legend>{lang}wcf.acp.user.sendMail.mail{/lang}</legend>
				
				<div>
					<dl id="subjectDiv"{if $errorField == 'subject'} class="formError"{/if}>
						<dt><label for="subject">{lang}wcf.acp.user.sendMail.subject{/lang}</label></dt>
						<dd>
							<input type="text" id="subject" name="subject" value="{$subject}" class="long" />
							{if $errorField == 'subject'}
								<small class="innerError">{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}</small>
							{/if}
							<small id="subjectHelpMessage">{lang}wcf.acp.user.sendMail.subject.description{/lang}</small>
						</dd>
					</dl>
					
					<dl id="fromDiv"{if $errorField == 'from'} class="formError"{/if}>
						<dt><label for="from">{lang}wcf.acp.user.sendMail.from{/lang}</label></dt>
						<dd>
							<input type="text" id="from" name="from" value="{$from}" class="medium" />
							{if $errorField == 'from'}
								<small class="innerError">{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}</small>
							{/if}
							<small id="fromHelpMessage">{lang}wcf.acp.user.sendMail.from.description{/lang}</small><!-- ToDo: Language variable contains paragraphs! -->
						</dd>
					</dl>
					
					<dl id="textDiv"{if $errorField == 'text'} class="formError"{/if}>
						<dt><label for="text">{lang}wcf.acp.user.sendMail.text{/lang}</label></dt>
						<dd>
							<textarea id="text" name="text" rows="15" cols="40" class="long">{$text}</textarea>
							{if $errorField == 'text'}
								<small class="innerError">{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}</small>
							{/if}
							<small id="textHelpMessage">{lang}wcf.acp.user.sendMail.text.description{/lang}</small>
						</dd>
					</dl>
					
					<dl>
						<dt></dt>
						<dd>
							<label><input type="checkbox" id="enableHTML" name="enableHTML" {if $enableHTML == 1}checked="checked" {/if}value="1" /> {lang}wcf.acp.user.sendMail.enableHTML{/lang}</label>
						</dd>
					</dl>
				</div>
				
				{if $additionalFields|isset}{@$additionalFields}{/if}
			</fieldset>
		</div>
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
