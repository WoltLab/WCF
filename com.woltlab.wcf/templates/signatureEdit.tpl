{include file='userMenuSidebar'}

{include file='header' __disableAds=true __sidebarLeftHasMenu=true}

{include file='shared_formError'}

{if $success|isset}
	<woltlab-core-notice type="success">{lang}wcf.global.success.edit{/lang}</woltlab-core-notice>
{/if}

{if $__wcf->user->disableSignature}
	<woltlab-core-notice type="error">{lang}wcf.user.signature.error.disabled{/lang}</woltlab-core-notice>
{/if}

<form method="post" action="{link controller='SignatureEdit'}{/link}">
	{if $signatureCache}
		<section class="section">
			<h2 class="sectionTitle">{lang}wcf.user.signature.current{/lang}</h2>
			
			<div class="htmlContent messageSignatureConstraints">{@$signatureCache}</div>
		</section>
	{/if}
	
	{if !$__wcf->user->disableSignature}
		<section class="section" id="signatureContainer">
			<h2 class="sectionTitle">{lang}wcf.user.signature{/lang}</h2>
				
			<dl class="wide{if $errorField == 'text'} formError{/if}">
				<dt><label for="text">{lang}wcf.user.signature{/lang}</label></dt>
				<dd class="messageSignatureConstraints">
					<textarea id="text" class="wysiwygTextarea" name="text" rows="20" cols="40"
						data-disable-media="true"
					>{$text}</textarea>
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
			<button type="button" id="previewButton" class="button jsOnly" accesskey="p">{lang}wcf.global.button.preview{/lang}</button>
			{csrfToken}
		</div>
	{/if}
</form>

<script data-relocate="true">
	$(function() {
		WCF.Language.addObject({
			'wcf.global.preview': '{jslang}wcf.global.preview{/jslang}'
		});
		
		new WCF.User.SignaturePreview('wcf\\data\\user\\UserProfileAction', 'text', 'previewButton');
	});
</script>

{include file='shared_wysiwyg'}
{include file='footer' __disableAds=true}
