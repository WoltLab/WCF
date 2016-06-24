{include file='header'}

{include file='formError'}

<form method="post" action="{link controller='Mail' object=$user}{/link}">
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.user.mail.information{/lang}</h2>
		
		<dl{if $errorField == 'subject'} class="formError"{/if}>
			<dt><label for="subject">{lang}wcf.user.mail.subject{/lang}</label></dt>
			<dd>
				<input type="text" id="subject" name="subject" value="{$subject}" required class="long">
				{if $errorField == 'subject'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}wcf.user.mail.subject.error.{@$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
		{if $__wcf->user->userID}
			<dl>
				<dt></dt>
				<dd><label><input type="checkbox" name="showAddress" value="1"{if $showAddress == 1} checked{/if}> {lang}wcf.user.mail.showAddress{/lang}</label></dd>
			</dl>
		{else}
			<dl{if $errorField == 'email'} class="formError"{/if}>
				<dt><label for="email">{lang}wcf.user.mail.senderEmail{/lang}</label></dt>
				<dd>
					<input type="email" id="email" name="email" value="{$email}" required class="medium">
					{if $errorField == 'email'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{elseif $errorType == 'notValid'}
								{lang}wcf.user.email.error.notValid{/lang}
							{else}
								{lang}wcf.user.mail.senderEmail.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>
		{/if}
		
		{event name='informationFields'}
	</section>
	
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.user.mail.message{/lang}</h2>
		
		<dl class="wide{if $errorField == 'message'} formError{/if}">
			<dd>
				<textarea rows="15" cols="40" name="message" id="message" required>{$message}</textarea>
				{if $errorField == 'message'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}wcf.user.mail.message.error.{@$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
		{event name='messageFields'}
	</section>
	
	{event name='sections'}
	
	{include file='captcha'}
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

{include file='footer'}
