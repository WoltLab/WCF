{if $recaptchaLegacyMode|empty}
	{include file='captcha'}
{else}
	{if RECAPTCHA_PUBLICKEY && RECAPTCHA_PRIVATEKEY}
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
										callback: resolve,
										theme: document.documentElement.dataset.colorScheme === "dark" ? "dark" : "light"
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
							'sitekey' : '{RECAPTCHA_PUBLICKEY|encodeJS}',
							theme: document.documentElement.dataset.colorScheme === "dark" ? "dark" : "light"
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
