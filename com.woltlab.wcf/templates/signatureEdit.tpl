{include file='userMenuSidebar'}

{include file='header'}

{include file='formError'}

{if $success|isset}
	<p class="success">{lang}wcf.global.success.edit{/lang}</p>
{/if}

{if $__wcf->user->disableSignature}
	<p class="error">{lang}wcf.user.signature.error.disabled{/lang}</p>
{/if}

<form method="post" action="{link controller='SignatureEdit'}{/link}">
	{if $signatureCache}
		<section class="section">
			<h2 class="sectionTitle">{lang}wcf.user.signature.current{/lang}</h2>
			
			{@$signatureCache}
		</section>
	{/if}
	
	{if !$__wcf->user->disableSignature}
		<section class="section" id="signatureContainer">
			<h2 class="sectionTitle">{lang}wcf.user.signature{/lang}</h2>
				
			<dl class="wide{if $errorField == 'text'} formError{/if}">
				<dt><label for="text">{lang}wcf.user.signature{/lang}</label></dt>
				<dd>
					<textarea id="text" name="text" rows="20" cols="40">{$text}</textarea>
					{if $errorField == 'text'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{elseif $errorType == 'tooLong'}
								{lang}wcf.message.error.tooLong{/lang}
							{elseif $errorType == 'censoredWordsFound'}
								{lang}wcf.message.error.censoredWordsFound{/lang}
							{elseif $errorType == 'disallowedBBCodes'}
								{lang}wcf.message.error.disallowedBBCodes{/lang}
							{else}
								{lang}wcf.user.signature.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>
			
			{event name='fields'}
		</section>
		
		{event name='sections'}
		
		{include file='messageFormTabs'}
	{/if}
	
	{if !$__wcf->user->disableSignature}
		<div class="formSubmit">
			<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
			<button id="previewButton" class="jsOnly" accesskey="p">{lang}wcf.global.button.preview{/lang}</button>
			{@SECURITY_TOKEN_INPUT_TAG}
		</div>
	{/if}
</form>

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		WCF.Language.addObject({
			'wcf.global.preview': '{lang}wcf.global.preview{/lang}'
		});
		
		new WCF.User.SignaturePreview('wcf\\data\\user\\UserProfileAction', 'text', 'previewButton');
	});
	//]]>
</script>

{include file='wysiwyg'}
{include file='footer'}
