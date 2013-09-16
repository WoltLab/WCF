<fieldset>
	<legend><label for="recaptcha_response_field">{lang}wcf.recaptcha.title{/lang}</label></legend>
	<small>{lang}wcf.recaptcha.description{/lang}</small>
	
	<dl class="wide reCaptcha{if $errorField == 'recaptchaString'} formError{/if}">
		<script data-relocate="true">
			//<![CDATA[
			var RecaptchaOptions = {
				lang: '{@$recaptchaLanguageCode}',
				theme : 'custom'
			}
			//]]>
		</script>
		<dt class="jsOnly">
			<label for="recaptcha_response_field">reCAPTCHA</label>
		</dt>
		<dd class="jsOnly">
			<div id="recaptcha_image" class="framed"></div>
			<input type="text" id="recaptcha_response_field" name="recaptcha_response_field" class="medium marginTop" />
			{if $errorField == 'recaptchaString'}
				<small class="innerError">
					{if $errorType == 'empty'}{lang}wcf.global.form.error.empty{/lang}{/if}
					{if $errorType == 'false'}{lang}wcf.recaptcha.error.recaptchaString.false{/lang}{/if}
				</small>
			{/if}
		</dd>
		
		{event name='fields'}
		
		<dd class="jsOnly">
			<ul class="buttonList smallButtons">
				<li><a href="javascript:Recaptcha.reload()" class="button small"><span class="icon icon16 icon-repeat"></span> <span>{lang}wcf.recaptcha.reload{/lang}</span></a></li>
				<li class="recaptcha_only_if_image"><a href="javascript:Recaptcha.switch_type('audio')" class="button small"><span class="icon icon16 icon-volume-up"></span> <span>{lang}wcf.recaptcha.audio{/lang}</span></a></li>
				<li class="recaptcha_only_if_audio"><a href="javascript:Recaptcha.switch_type('image')" class="button small"><span class="icon icon16 icon-eye-open"></span> <span>{lang}wcf.recaptcha.image{/lang}</span></a></li>
				<li><a href="javascript:Recaptcha.showhelp()" class="button small"><span class="icon icon16 icon-question-sign"></span> <span>{lang}wcf.recaptcha.help{/lang}</span></a></li>
				{event name='buttons'}
			</ul>
		</dd>
		
		<script data-relocate="true" src="http{if $recaptchaUseSSL}s{/if}://www.google.com/recaptcha/api/challenge?k={$recaptchaPublicKey}"></script>
		<noscript>
			<dd>
				<iframe src="http{if $recaptchaUseSSL}s{/if}://www.google.com/recaptcha/api/noscript?k={$recaptchaPublicKey}" height="300" width="500" seamless="seamless"></iframe><br />
				<textarea name="recaptcha_challenge_field" rows="3" cols="40"></textarea>
				<input type="hidden" name="recaptcha_response_field" value="manual_challenge" />
			</dd>
			{if $errorField == 'recaptchaString'}
				<small class="innerError">
					{if $errorType == 'empty'}{lang}wcf.global.form.error.empty{/lang}{/if}
					{if $errorType == 'false'}{lang}wcf.recaptcha.error.recaptchaString.false{/lang}{/if}
				</small>
			{/if}
		</noscript>
	</dl>
</fieldset>
