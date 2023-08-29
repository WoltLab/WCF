{include file='header'}

<form method="post" action="install.php">
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.global.systemRequirements.required{/lang}</h2>
		
		<section class="section">
			<h2 class="sectionTitle">{lang}wcf.global.systemRequirements.php{/lang}</h2>
			
			<div class="row rowColGap formGrid">
				<dl class="col-xs-12 col-md-6">
					<dt>{lang}wcf.global.systemRequirements.element.required{/lang}</dt>
					<dd>8.1.2</dd>
				</dl>
				
				<dl class="col-xs-12 col-md-6">
					<dt>{lang}wcf.global.systemRequirements.element.yours{/lang}</dt>
					<dd>
						<span class="badge {if !$system.phpVersion.result}red{else}green{/if}">{$system.phpVersion.value}</span>
						{if !$system.phpVersion.result}<small>{lang}wcf.global.systemRequirements.php.description{/lang}</small>{/if}
					</dd>
				</dl>
			</div>
		</section>
		
		<section class="section">
			<h2 class="sectionTitle">{lang}wcf.global.systemRequirements.x64{/lang}</h2>
			
			<div class="row rowColGap formGrid">
				<dl class="col-xs-12 col-md-6">
					<dt>{lang}wcf.global.systemRequirements.element.required{/lang}</dt>
					<dd>{lang}wcf.global.systemRequirements.active{/lang}</dd>
				</dl>
				
				<dl class="col-xs-12 col-md-6">
					<dt>{lang}wcf.global.systemRequirements.element.yours{/lang}</dt>
					<dd>
						<span class="badge {if !$system.x64.result}red{else}green{/if}">
						{if !$system.x64.result}{lang}wcf.global.systemRequirements.notActive{/lang}{else}
							{lang}wcf.global.systemRequirements.active{/lang}
						{/if}</span>
						{if !$system.x64.result}<small>{lang}wcf.global.systemRequirements.x64.description{/lang}</small>{/if}
					</dd>
				</dl>
			</div>
		</section>
		
		<section class="section">
			<h2 class="sectionTitle">{lang}wcf.global.systemRequirements.memoryLimit{/lang}</h2>
			
			<div class="row rowColGap formGrid">
				<dl class="col-xs-12 col-md-6">
					<dt>{lang}wcf.global.systemRequirements.element.required{/lang}</dt>
					<dd>128 MiB</dd>
				</dl>
				
				<dl class="col-xs-12 col-md-6">
					<dt>{lang}wcf.global.systemRequirements.element.yours{/lang}</dt>
					<dd>
						<span class="badge {if !$system.memoryLimit.result}red{else}green{/if}">{$system.memoryLimit.value|filesizeBinary}</span>
						{if !$system.memoryLimit.result}<small>{lang}wcf.global.systemRequirements.memoryLimit.description{/lang}</small>{/if}
					</dd>
				</dl>
			</div>
		</section>
		
		<section class="section">
			<h2 class="sectionTitle">{lang}wcf.global.systemRequirements.sql{/lang}</h2>
			
			<div class="row rowColGap formGrid">
				<dl class="col-xs-12 col-md-6">
					<dt>{lang}wcf.global.systemRequirements.element.required{/lang}</dt>
					<dd>{lang}wcf.global.systemRequirements.active{/lang}</dd>
				</dl>
				
				<dl class="col-xs-12 col-md-6">
					<dt>{lang}wcf.global.systemRequirements.element.yours{/lang}</dt>
					<dd>
						<span class="badge {if !$system.sql.result}red{else}green{/if}">
						{if !$system.sql.result}{lang}wcf.global.systemRequirements.sql.notFound{/lang}{else}
							pdo_mysql
						{/if}</span>
						{if !$system.sql.result}<small>{lang}wcf.global.systemRequirements.sql.description{/lang}</small>{/if}
					</dd>
				</dl>
			</div>
		</section>
		
		<section class="section">
			<h2 class="sectionTitle">{lang}wcf.global.systemRequirements.graphicsLibrary{/lang}</h2>
			
			<div class="row rowColGap formGrid">
				<dl class="col-xs-12 col-md-6">
					<dt>{lang}wcf.global.systemRequirements.element.required{/lang}</dt>
					<dd>{lang}wcf.global.systemRequirements.graphicsLibrary.requirement{/lang}</dd>
				</dl>
				
				<dl class="col-xs-12 col-md-6">
					<dt>{lang}wcf.global.systemRequirements.element.yours{/lang}</dt>
					<dd>
						{if $system.graphicsLibrary.result}
							<span class="badge green">{$system.graphicsLibrary.value}</span>
						{else}
							<span class="badge red">
								{* If the result is `false`, but the `value` is present, then the library failed the webp check. *}
								{if $system.graphicsLibrary.value}
									{lang}wcf.global.systemRequirements.graphicsLibrary.webp{/lang}
								{else}
									{lang}wcf.global.systemRequirements.graphicsLibrary.notFound{/lang}
								{/if}
							</span>
							<small>{lang}wcf.global.systemRequirements.graphicsLibrary.description{/lang}</small>
						{/if}
					</dd>
				</dl>
			</div>
		</section>
		
		<section class="section">
			<h2 class="sectionTitle">{lang}wcf.global.systemRequirements.hostname{/lang}</h2>
			
			<div class="row rowColGap formGrid">
				<dl class="col-xs-12 col-md-6">
					<dt>{lang}wcf.global.systemRequirements.element.required{/lang}</dt>
					<dd>{lang}wcf.global.systemRequirements.hostname.requirement{/lang}</dd>
				</dl>
				
				<dl class="col-xs-12 col-md-6">
					<dt>{lang}wcf.global.systemRequirements.element.yours{/lang}</dt>
					<dd>
						<span id="hostnameBadge" class="badge {if !$system.hostname.result}red{else}green{/if}">{$system.hostname.value}</span>
						<small{if $system.hostname.result} style="display: none"{/if}>{lang}wcf.global.systemRequirements.hostname.description{/lang}</small>
					</dd>
					<script>
						(function () {
							var badge = document.getElementById('hostnameBadge');
							if (badge.classList.contains('green')) {
								var serverHost = badge.textContent;
								var browserHost = window.location.host;
								if (serverHost != browserHost) {
									badge.classList.remove('green');
									badge.classList.add('red');
									badge.nextElementSibling.style.display = '';
									document.querySelector('.formSubmit input[type="submit"]').disabled = true;
								}
							}
						})();
					</script>
				</dl>
			</div>
		</section>
		
		<section class="section">
			<h2 class="sectionTitle">{lang}wcf.global.systemRequirements.cookie{/lang}</h2>
			
			<div class="row rowColGap formGrid">
				<dl class="col-xs-12 col-md-6">
					<dt>{lang}wcf.global.systemRequirements.element.required{/lang}</dt>
					<dd>{lang}wcf.global.systemRequirements.active{/lang}</dd>
				</dl>
				
				<dl class="col-xs-12 col-md-6">
					<dt>{lang}wcf.global.systemRequirements.element.yours{/lang}</dt>
					<dd>
						<span class="badge {if !$system.cookie.result}red{else}green{/if}">
						{if !$system.cookie.result}{lang}wcf.global.systemRequirements.notActive{/lang}{else}
							{lang}wcf.global.systemRequirements.active{/lang}
						{/if}</span>
						{if !$system.cookie.result}<small>{lang}wcf.global.systemRequirements.cookie.description{/lang}</small>{/if}
					</dd>
				</dl>
			</div>
		</section>
	</section>
	
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.global.systemRequirements.recommended{/lang}</h2>
		
		<section class="section">
			<h2 class="sectionTitle">{lang}wcf.global.systemRequirements.tls{/lang}</h2>
			
			<div class="row rowColGap formGrid">
				<dl class="col-xs-12 col-md-6">
					<dt>{lang}wcf.global.systemRequirements.element.recommended{/lang}</dt>
					<dd>{lang}wcf.global.systemRequirements.active{/lang}</dd>
				</dl>
				
				<dl class="col-xs-12 col-md-6">
					<dt>{lang}wcf.global.systemRequirements.element.yours{/lang}</dt>
					<dd>
						<span class="badge {if !$system.tls.result}red{else}green{/if}">
						{if !$system.tls.result}{lang}wcf.global.systemRequirements.notActive{/lang}{else}
							{lang}wcf.global.systemRequirements.active{/lang}
						{/if}</span>
						{if !$system.tls.result}<small>{lang}wcf.global.systemRequirements.tls.description{/lang}</small>{/if}
					</dd>
				</dl>
			</div>
		</section>
		
		<section class="section">
			<h2 class="sectionTitle">{lang}wcf.global.systemRequirements.uploadMaxFilesize{/lang}</h2>
			
			<div class="row rowColGap formGrid">
				<dl class="col-xs-12 col-md-6">
					<dt>{lang}wcf.global.systemRequirements.element.recommended{/lang}</dt>
					<dd>&gt; 0</dd>
				</dl>
				
				<dl class="col-xs-12 col-md-6">
					<dt>{lang}wcf.global.systemRequirements.element.yours{/lang}</dt>
					<dd>
						<span class="badge {if !$system.uploadMaxFilesize.result}yellow{else}green{/if}">{$system.uploadMaxFilesize.value}</span>
						{if !$system.uploadMaxFilesize.result}<small>{lang}wcf.global.systemRequirements.uploadMaxFilesize.description{/lang}</small>{/if}
					</dd>
				</dl>
			</div>
		</section>
		
		<section class="section">
			<h2 class="sectionTitle">{lang}wcf.global.systemRequirements.openSSL{/lang}</h2>
			
			<div class="row rowColGap formGrid">
				<dl class="col-xs-12 col-md-6">
					<dt>{lang}wcf.global.systemRequirements.element.recommended{/lang}</dt>
					<dd>{lang}wcf.global.systemRequirements.active{/lang}</dd>
				</dl>
				
				<dl class="col-xs-12 col-md-6">
					<dt>{lang}wcf.global.systemRequirements.element.yours{/lang}</dt>
					<dd>
						<span class="badge {if !$system.openssl.result}red{else}green{/if}">
						{if !$system.openssl.result}{lang}wcf.global.systemRequirements.notActive{/lang}{else}
							{lang}wcf.global.systemRequirements.active{/lang}
						{/if}</span>
						{if !$system.openssl.result}<small>{lang}wcf.global.systemRequirements.openSSL.description{/lang}</small>{/if}
					</dd>
				</dl>
			</div>
		</section>
		
		<section class="section">
			<h2 class="sectionTitle">{lang}wcf.global.systemRequirements.curl{/lang}</h2>
			
			<div class="row rowColGap formGrid">
				<dl class="col-xs-12 col-md-6">
					<dt>{lang}wcf.global.systemRequirements.element.recommended{/lang}</dt>
					<dd>{lang}wcf.global.systemRequirements.active{/lang}</dd>
				</dl>
				
				<dl class="col-xs-12 col-md-6">
					<dt>{lang}wcf.global.systemRequirements.element.yours{/lang}</dt>
					<dd>
						<span class="badge {if !$system.curl.result}red{else}green{/if}">
						{if !$system.curl.result}{lang}wcf.global.systemRequirements.notActive{/lang}{else}
							{lang}wcf.global.systemRequirements.active{/lang}
						{/if}</span>
						{if !$system.curl.result}<small>{lang}wcf.global.systemRequirements.curl.description{/lang}</small>{/if}
					</dd>
				</dl>
			</div>
		</section>
	</section>

	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.next{/lang}"{if !$system.phpVersion.result || !$system.x64.result || !$system.sql.result || !$system.memoryLimit.result || !$system.graphicsLibrary.result || !$system.hostname.result || !$system.cookie.result} disabled{/if} accesskey="s">
		<input type="hidden" name="step" value="{$nextStep}">
		<input type="hidden" name="tmpFilePrefix" value="{$tmpFilePrefix}">
		<input type="hidden" name="languageCode" value="{$languageCode}">
		<input type="hidden" name="dev" value="{$developerMode}">
	</div>
</form>

<script>
if (typeof window._trackWcfSetupStep === 'function') window._trackWcfSetupStep('showSystemRequirements');
</script>
{include file='footer'}
