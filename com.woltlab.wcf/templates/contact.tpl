{include file='header'}

{include file='formError'}

<form method="post" action="{link controller='Contact'}{/link}">
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.contact.sender.information{/lang}</h2>
		
		<dl{if $errorField == 'name'} class="formError"{/if}>
			<dt><label for="name">{lang}wcf.contact.sender{/lang}</label> <span class="customOptionRequired">*</span></dt>
			<dd>
				<input type="text" id="name" name="name" value="{$name}" required class="long">
				{if $errorField == 'name'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}wcf.contact.sender.error.{@$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
		<dl{if $errorField == 'email'} class="formError"{/if}>
			<dt><label for="email">{lang}wcf.user.email{/lang}</label> <span class="customOptionRequired">*</span></dt>
			<dd>
				<input type="email" id="email" name="email" value="{$email}" required class="medium">
				{if $errorField == 'email'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}wcf.user.email.error.{@$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
		{event name='informationFields'}
	</section>
	
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.contact.data{/lang}</h2>
		
		{if $recipientList|count > 1}
			<dl{if $errorField == 'recipientID'} class="formError"{/if}>
				<dt><label for="recipientID">{lang}wcf.contact.recipientID{/lang}</label> <span class="customOptionRequired">*</span></dt>
				<dd>
					<select name="recipientID" id="recipientID" required>
						<option value="">{lang}wcf.global.noSelection{/lang}</option>
						{foreach from=$recipientList item=recipient}
							<option value="{@$recipient->recipientID}"{if $recipient->recipientID == $recipientID} selected{/if}>{$recipient}</option>
						{/foreach}
					</select>
					{if $errorField == 'recipientID'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.contact.recipientID.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>
		{/if}
		
		{include file='customOptionFieldList'}
		
		{event name='optionFields'}
		
		{if CONTACT_FORM_ENABLE_ATTACHMENTS && !$attachmentHandler|empty && $attachmentHandler->canUpload()}
			<div class="contactFormAttachments">
				{include file='messageFormAttachments' wysiwygSelector=''}
			</div>
		{/if}
		
		<p class="info">{lang}wcf.contact.confirmPrivacyPolicy{/lang}</p>
	</section>
	
	{event name='sections'}
	
	{include file='captcha' supportsAsyncCaptcha=true}
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		{csrfToken}
	</div>
	
	<div class="section">
		<p><span class="customOptionRequired">*</span> {lang}wcf.contact.options.required{/lang}</p>
	</div>
</form>

{include file='footer'}
