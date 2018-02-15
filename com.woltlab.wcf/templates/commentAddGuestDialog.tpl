<div>
	<div class="section">
		<dl{if $errorType[username]|isset} class="formError"{/if}>
			<dt><label for="username">{lang}wcf.user.username{/lang}</label></dt>
			<dd>
				<input type="text" id="username" name="username" value="{$username}" required class="long" autofocus="true">
				{if $errorType[username]|isset}
					<small class="innerError">
						{if $errorType[username] == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}wcf.user.username.error.{$errorType[username]}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
		<dl{if $errorType[privacyPolicyConsent]|isset} class="formError"{/if}>
			<dt><label for="privacyPolicyConsent">{lang}wcf.comment.privacyPolicyConsent.title{/lang}</label></dt>
			<dd>
				<label><input type="checkbox" id="privacyPolicyConsent" name="privacyPolicyConsent" value="1"> {lang}wcf.comment.privacyPolicyConsent.text{/lang}</label>
				{if $errorType[privacyPolicyConsent]|isset}
					<small class="innerError">
						{if $errorType[privacyPolicyConsent] == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
	</div>
	
	{include file='captcha'}
</div>

<div class="formSubmit">
	<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
	<button data-type="cancel">{lang}wcf.global.button.cancel{/lang}</button>
</div>
