{if $recaptchaLegacyMode|empty}
	{include file='captcha'}
{else}
	{* No explicit keys were set, use legacy V1 API and WoltLab's OEM keys *}
	{if RECAPTCHA_PUBLICKEY === '' || RECAPTCHA_PRIVATEKEY === ''}
	<section class="section">
		<header class="sectionHeader">
			<h2 class="sectionTitle">{lang}wcf.recaptcha.title{/lang}</h2>
			<p class="sectionDescription">{lang}wcf.recaptcha.description{/lang}</p>
		</header>
		
		<dl class="wide reCaptcha{if $errorField|isset && $errorField == 'recaptchaString'} formError{/if}">
			{if !$ajaxCaptcha|isset || !$ajaxCaptcha}
				<script data-relocate="true">
					var RecaptchaOptions = {
						lang: '{@$recaptchaLanguageCode}',
						theme : 'custom'
					}
				</script>
			{/if}
			<dt class="jsOnly">
				<label for="recaptcha_response_field">reCAPTCHA</label>
			</dt>
			<dd class="jsOnly">
				<div id="recaptcha_image"></div>
				<input type="text" id="recaptcha_response_field" name="recaptcha_response_field" class="medium">
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
					<li><a href="javascript:Recaptcha.reload()" class="button small"><span class="icon icon16 fa-repeat"></span> <span>{lang}wcf.recaptcha.reload{/lang}</span></a></li>
					<li class="recaptcha_only_if_image"><a href="javascript:Recaptcha.switch_type('audio')" class="button small"><span class="icon icon16 fa-volume-up"></span> <span>{lang}wcf.recaptcha.audio{/lang}</span></a></li>
					<li class="recaptcha_only_if_audio"><a href="javascript:Recaptcha.switch_type('image')" class="button small"><span class="icon icon16 fa-eye"></span> <span>{lang}wcf.recaptcha.image{/lang}</span></a></li>
					<li><a href="javascript:Recaptcha.showhelp()" class="button small"><span class="icon icon16 fa-question"></span> <span>{lang}wcf.recaptcha.help{/lang}</span></a></li>
					{event name='buttons'}
				</ul>
			</dd>
			
			{if !$ajaxCaptcha|isset || !$ajaxCaptcha}
				<script data-relocate="true" src="//www.google.com/recaptcha/api/challenge?k={$recaptchaPublicKey}"></script>
				<noscript>
					<dd>
						<iframe src="//www.google.com/recaptcha/api/noscript?k={$recaptchaPublicKey}" height="300" width="500" seamless="seamless"></iframe><br>
						<textarea name="recaptcha_challenge_field" rows="3" cols="40"></textarea>
						<input type="hidden" name="recaptcha_response_field" value="manual_challenge">
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
				</script>
			{/if}
		</dl>
	</section>
	{else}
		{if $supportsAsyncCaptcha|isset && $supportsAsyncCaptcha && RECAPTCHA_PUBLICKEY_INVISIBLE && RECAPTCHA_PRIVATEKEY_INVISIBLE}
		<section class="section">
			<h2 class="sectionTitle">{lang}wcf.recaptcha.title{/lang}</h2>
			{assign var="recaptchaBucketID" value=true|microtime|sha1}
			<dl class="{if $errorField|isset && $errorField == 'recaptchaString'}formError{/if}">
				<dt></dt>
				<dd>
					<input type="hidden" name="recaptcha-type" value="invisible">
					<div id="recaptchaBucket{$recaptchaBucketID}"></div>
					<noscript>
						<div style="width: 302px; height: 473px;">
							<div style="width: 302px; height: 422px; position: relative;">
								<div style="width: 302px; height: 422px; position: relative;">
									<iframe src="https://www.google.com/recaptcha/api/fallback?k={RECAPTCHA_PUBLICKEY_INVISIBLE|encodeJS}" frameborder="0" scrolling="no" style="width: 302px; height:422px; border-style: none;"></iframe>
								</div>
								<div style="width: 300px; height: 60px; position: relative; border-style: none; bottom: 12px; left: 0; margin: 0px; padding: 0px; right: 25px; background: #f9f9f9; border: 1px solid #c1c1c1; border-radius: 3px;">
									<textarea name="g-recaptcha-response" class="g-recaptcha-response" style="width: 290px; height: 50px; border: 1px solid #c1c1c1; margin: 5px; padding: 0px; resize: none;"></textarea>
								</div>
							</div>
						</div>
					</noscript>
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
								{lang}wcf.captcha.recaptchaInvisible.error.recaptchaString.{$__errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>
			<script data-relocate="true">
			if (!WCF.recaptcha) {
				WCF.recaptcha = {
					queue: [],
					callbackCalled: false,
					mapping: { }
				};
				
				// this needs to be in global scope
				function recaptchaCallback() {
					var bucketId;
					WCF.recaptcha.callbackCalled = true;
					
					// clear queue
					while (config = WCF.recaptcha.queue.shift()) {
						(function (config) {
							var bucketId = config.bucket;
							
							require(['Dom/Traverse', 'Dom/Util'], function (DomTraverse, DomUtil) {
								var bucket = elById(bucketId);
								
								var promise = new Promise(function (resolve, reject) {
									WCF.recaptcha.mapping['recaptchaBucket{$recaptchaBucketID}'] = grecaptcha.render(bucket, {
										sitekey: '{RECAPTCHA_PUBLICKEY_INVISIBLE|encodeJS}',
										size: 'invisible',
										badge: 'inline',
										callback: resolve
									});
								});
								
								if (config.ajaxCaptcha) {
									WCF.System.Captcha.addCallback(config.ajaxCaptcha, function() {
										grecaptcha.execute(WCF.recaptcha.mapping['recaptchaBucket{$recaptchaBucketID}']);
										return promise.then(function (token) {
											return {
												'g-recaptcha-response': token,
												'recaptcha-type': 'invisible'
											};
										});
									});
								}
								else {
									var form = DomTraverse.parentByTag(bucket, 'FORM');
									
									var pressed = undefined;
									elBySelAll('input[type=submit]', form, function (button) {
										button.addEventListener('click', function (event) {
											pressed = button;
										});
									});
									
									var listener = function (event) {
										event.preventDefault();
										promise.then(function (token) {
											form.removeEventListener('submit', listener);
											pressed.disabled = false;
											pressed.click();
										});
										grecaptcha.execute(WCF.recaptcha.mapping['recaptchaBucket{$recaptchaBucketID}']);
									}
									form.addEventListener('submit', listener);
								}
								
							});
						})(config);
					}
				}
			}
			
			// add captcha to queue
			WCF.recaptcha.queue.push({
				bucket: 'recaptchaBucket{$recaptchaBucketID}'
				{if $ajaxCaptcha|isset && $ajaxCaptcha}
					, ajaxCaptcha: '{$captchaID}'
				{/if}
			});
			
			// trigger callback immediately, if API already is available
			if (WCF.recaptcha.callbackCalled) setTimeout(recaptchaCallback, 1);
			
			// ensure recaptcha API is loaded at most once
			if (!window.grecaptcha) $.getScript('https://www.google.com/recaptcha/api.js?render=explicit&onload=recaptchaCallback');
			</script>
		</section>
		{else}
		<section class="section">
			<h2 class="sectionTitle">{lang}wcf.recaptcha.title{/lang}</h2>
			{assign var="recaptchaBucketID" value=true|microtime|sha1}
			<dl class="{if $errorField|isset && $errorField == 'recaptchaString'}formError{/if}">
				<dt></dt>
				<dd>
				<input type="hidden" name="recaptcha-type" value="v2">
					<div id="recaptchaBucket{$recaptchaBucketID}"></div>
					<noscript>
						<div style="width: 302px; height: 473px;">
							<div style="width: 302px; height: 422px; position: relative;">
								<div style="width: 302px; height: 422px; position: relative;">
									<iframe src="https://www.google.com/recaptcha/api/fallback?k={RECAPTCHA_PUBLICKEY|encodeJS}" frameborder="0" scrolling="no" style="width: 302px; height:422px; border-style: none;"></iframe>
								</div>
								<div style="width: 300px; height: 60px; position: relative; border-style: none; bottom: 12px; left: 0; margin: 0px; padding: 0px; right: 25px; background: #f9f9f9; border: 1px solid #c1c1c1; border-radius: 3px;">
									<textarea name="g-recaptcha-response" class="g-recaptcha-response" style="width: 290px; height: 50px; border: 1px solid #c1c1c1; margin: 5px; padding: 0px; resize: none;"></textarea>
								</div>
							</div>
						</div>
					</noscript>
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
					'g-recaptcha-response': grecaptcha.getResponse(WCF.recaptcha.mapping['recaptchaBucket{$recaptchaBucketID}']),
					'type': 'v2'
				};
			});
			{/if}
			
			// ensure recaptcha API is loaded at most once
			if (!window.grecaptcha) $.getScript('https://www.google.com/recaptcha/api.js?render=explicit&onload=recaptchaCallback');
			</script>
		</section>
		{/if}
	{/if}
{/if}
