{if $recaptchaLegacyMode|empty}
	{include file='captcha'}
{else}
	{* No explicit keys were set, use legacy V1 API and WoltLab's OEM keys *}
	{if RECAPTCHA_PUBLICKEY === '' || RECAPTCHA_PRIVATEKEY === ''}
	<fieldset>
		<legend><label for="recaptcha_response_field">{lang}wcf.recaptcha.title{/lang}</label></legend>
		<small>{lang}wcf.recaptcha.description{/lang}</small>
		
		<dl class="wide reCaptcha{if $errorField|isset && $errorField == 'recaptchaString'} formError{/if}">
			{if !$ajaxCaptcha|isset || !$ajaxCaptcha}
				<script data-relocate="true">
					//<![CDATA[
					var RecaptchaOptions = {
						lang: '{@$recaptchaLanguageCode}',
						theme : 'custom'
					}
					//]]>
				</script>
			{/if}
			<dt class="jsOnly">
				<label for="recaptcha_response_field">reCAPTCHA</label>
			</dt>
			<dd class="jsOnly">
				<div id="recaptcha_image" class="framed"></div>
				<input type="text" id="recaptcha_response_field" name="recaptcha_response_field" class="medium marginTop" />
				{if (($errorType|isset && $errorType|is_array && $errorType[recaptchaString]|isset) || ($errorField|isset && $errorField == 'recaptchaString'))}
					{if $errorType|is_array && $errorType[recaptchaString]|isset}
						{assign var='__errorType' value=$errorType[recaptchaString]}
					{else}
						{assign var='__errorType' value=$errorType}
					{/if}
					<small class="innerError">
						{if $__errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}wcf.recaptcha.error.recaptchaString.{$__errorType}{/lang}
						{/if}
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
			
			{if !$ajaxCaptcha|isset || !$ajaxCaptcha}
				<script data-relocate="true" src="//www.google.com/recaptcha/api/challenge?k={$recaptchaPublicKey}"></script>
				<noscript>
					<dd>
						<iframe src="//www.google.com/recaptcha/api/noscript?k={$recaptchaPublicKey}" height="300" width="500" seamless="seamless"></iframe><br />
						<textarea name="recaptcha_challenge_field" rows="3" cols="40"></textarea>
						<input type="hidden" name="recaptcha_response_field" value="manual_challenge" />
					</dd>
					{if (($errorType|isset && $errorType|is_array && $errorType[recaptchaString]|isset) || ($errorField|isset && $errorField == 'recaptchaString'))}
						{if $errorType|is_array && $errorType[recaptchaString]|isset}
							{assign var='__errorType' value=$errorType[recaptchaString]}
						{else}
							{assign var='__errorType' value=$errorType}
						{/if}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.recaptcha.error.recaptchaString.{$__errorType}{/lang}
							{/if}
						</small>
					{/if}
				</noscript>
			{else}
				<script data-relocate="true">
					//<![CDATA[
					$.getScript('//www.google.com/recaptcha/api/js/recaptcha_ajax.js', function() {
						Recaptcha.create("{$recaptchaPublicKey}", "recaptcha_image", {
							lang: '{@$recaptchaLanguageCode}',
							theme : 'custom'
						});
						
						WCF.System.Captcha.addCallback('{$captchaID}', function() {
							return {
								recaptcha_challenge_field: Recaptcha.get_challenge(),
								recaptcha_response_field: Recaptcha.get_response()
							};
						});
					});
					//]]>
				</script>
			{/if}
		</dl>
	</fieldset>
	{else}
	<fieldset>
		<legend>{lang}wcf.recaptcha.title{/lang}</legend>
		{assign var="recaptchaBucketID" value=true|microtime|sha1}
		<dl class="{if $errorField|isset && $errorField == 'recaptchaString'}formError{/if}">
			<dt></dt>
			<dd>
				<div id="recaptchaBucket{$recaptchaBucketID}"></div>
				{if (($errorType|isset && $errorType|is_array && $errorType[recaptchaString]|isset) || ($errorField|isset && $errorField == 'recaptchaString'))}
					{if $errorType|is_array && $errorType[recaptchaString]|isset}
						{assign var='__errorType' value=$errorType[recaptchaString]}
					{else}
						{assign var='__errorType' value=$errorType}
					{/if}
					<small class="innerError">
						{if $__errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}wcf.captcha.recaptchaV2.error.recaptchaString.{$__errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		<script data-relocate="true">
		//<![CDATA[
		if (!WCF.recaptcha) {
			WCF.recaptcha = {
				queue: [],
				callbackCalled: false,
				mapping: { }
			};
			
			// this needs to be in global scope
			function recaptchaCallback() {
				var bucket;
				WCF.recaptcha.callbackCalled = true;
				
				// clear queue
				while (bucket = WCF.recaptcha.queue.shift()) {
					WCF.recaptcha.mapping[bucket] = grecaptcha.render(bucket, {
						'sitekey' : '{RECAPTCHA_PUBLICKEY|encodeJS}'
					});
				}
			}
		}
		
		// add captcha to queue
		WCF.recaptcha.queue.push('recaptchaBucket{$recaptchaBucketID}');
		
		// trigger callback immediately, if API already is available
		if (WCF.recaptcha.callbackCalled) setTimeout(recaptchaCallback, 1);
		
		{if $ajaxCaptcha|isset && $ajaxCaptcha}
		WCF.System.Captcha.addCallback('{$captchaID}', function() {
			return {
				'g-recaptcha-response': grecaptcha.getResponse(WCF.recaptcha.mapping['recaptchaBucket{$recaptchaBucketID}'])
			};
		});
		{/if}
		
		// ensure recaptcha API is loaded at most once
		if (!window.grecaptcha) $.getScript('https://www.google.com/recaptcha/api.js?render=explicit&onload=recaptchaCallback');
		//]]>
		</script>
	</fieldset>
	{/if}
{/if}
