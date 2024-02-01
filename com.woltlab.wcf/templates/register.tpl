{if !REGISTER_DISABLED}
	{capture assign='contentDescription'}{lang}wcf.user.register.existingUser{/lang}{/capture}
{/if}

{include file='authFlowHeader'}

{if $isExternalAuthentication}
	<woltlab-core-notice type="info">{lang}wcf.user.3rdparty.{$__wcf->session->getVar('__3rdPartyProvider')}.register{/lang}</woltlab-core-notice>
{/if}

{include file='shared_formError'}

<form id="registerForm" method="post" action="{link controller='Register'}{/link}">
	<section class="section" hidden>
		<header class="sectionHeader">
			<h2 class="sectionTitle">{lang}wcf.user.register.honeyPot{/lang}</h2>
			<p class="sectionDescription">{lang}wcf.user.register.honeyPot.description{/lang}</p>
		</header>
		
		<dl>
			<dt>
				<label for="username">{lang}wcf.user.username{/lang}</label>
			</dt>
			<dd>
				<input type="text" id="username" name="username" value="" autocomplete="off" class="long" tabindex="998">
			</dd>
		</dl>
		
		<dl>
			<dt>
				<label for="email">{lang}wcf.user.email{/lang}</label>
			</dt>
			<dd>
				<input type="email" id="email" name="email" value="" autocomplete="off" class="long" tabindex="999">
			</dd>
		</dl>
		
		{event name='honeyPotFields'}
	</section>
	
	<div class="section">
		<dl{if $errorType[username]|isset} class="formError"{/if}>
			<dt>
				<label for="{@$randomFieldNames[username]}">{lang}wcf.user.username{/lang}</label> <span class="formFieldRequired">*</span>
			</dt>
			<dd>
				<input
					type="text"
					id="{@$randomFieldNames[username]}"
					name="{@$randomFieldNames[username]}"
					value="{$username}"
					required
					class="long"
					autocomplete="username"
					data-validation-endpoint="{$usernameValidationEndpoint}"
				>
				{if $errorType[username]|isset}
					<small class="innerError">
						{if $errorType[username] == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}wcf.user.username.error.{$errorType[username]}{/lang}
						{/if}
					</small>
				{/if}
				<small>{lang}wcf.user.username.description{/lang}</small>
			</dd>
		</dl>
		
		<dl{if $errorType[email]|isset} class="formError"{/if}>
			<dt>
				<label for="{@$randomFieldNames[email]}">{lang}wcf.user.email{/lang}</label> <span class="formFieldRequired">*</span>
			</dt>
			<dd>
				<input
					type="email"
					id="{@$randomFieldNames[email]}"
					name="{@$randomFieldNames[email]}"
					value="{$email}"
					required
					class="long"
					autocomplete="email"
					data-validation-endpoint="{$emailValidationEndpoint}"
				>
				{if $errorType[email]|isset}
					<small class="innerError">
						{if $errorType[email] == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}wcf.user.email.error.{$errorType[email]}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>

		{if !$isExternalAuthentication}
			<dl{if $errorType[password]|isset} class="formError"{/if}>
				<dt>
					<label for="{@$randomFieldNames[password]}">{lang}wcf.user.password{/lang}</label> <span class="formFieldRequired">*</span>
				</dt>
				<dd>
					<input
						type="password"
						id="{@$randomFieldNames[password]}"
						name="{@$randomFieldNames[password]}"
						value="{$password}"
						required
						class="long"
						autocomplete="new-password"
						passwordrules="{$passwordRulesAttributeValue}"
					>
					{if $errorType[password]|isset}
						<small class="innerError">
							{if $errorType[password] == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.user.password.error.{$errorType[password]}{/lang}
							{/if}
						</small>
					{/if}
					<small>{lang}wcf.user.password.description{/lang}</small>
				</dd>
			</dl>
		{/if}

		{if $availableLanguages|count > 1}
			<dl>
				<dt><label for="languageID">{lang}wcf.user.language.description{/lang}</label></dt>
				<dd id="languageIDContainer">
					<script data-relocate="true">
						require(['WoltLabSuite/Core/Language/Chooser'], ({ init }) => {
							const languages = {
								{implode from=$availableLanguages item=language}
								'{@$language->languageID}': {
									iconPath: '{@$language->getIconPath()|encodeJS}',
									languageName: '{@$language|encodeJS}'
								}
								{/implode}
							};
							
							init('languageIDContainer', 'languageID', {@$languageID}, languages);
						});
					</script>
					<noscript>
						<select name="languageID" id="languageID">
							{foreach from=$availableLanguages item=language}
								<option value="{$language->languageID}"{if $language->languageID == $languageID} selected{/if}>{$language}</option>
							{/foreach}
						</select>
					</noscript>
				</dd>
			</dl>
				
			{hascontent}
				<dl>
					<dt><label>{lang}wcf.user.visibleLanguages{/lang}</label></dt>
					<dd class="floated">
					{content}
						{foreach from=$availableContentLanguages item=language}
							<label><input name="visibleLanguages[]" type="checkbox" value="{$language->languageID}"{if $language->languageID|in_array:$visibleLanguages} checked{/if}> {$language}</label>
						{/foreach}
					{/content}
					<small>{lang}wcf.user.visibleLanguages.description{/lang}</small></dd>
				</dl>
			{/hascontent}
		{/if}

		{if REGISTER_ENABLE_DISCLAIMER}
			<dl{if $errorType[termsConfirmed]|isset} class="formError"{/if}>
				<dt></dt>
				<dd>
					<label>
						<input type="checkbox" name="termsConfirmed" value="1" required>
						{lang}wcf.user.register.confirmTerms{/lang}
						<span class="formFieldRequired">*</span>	
					</label>
					{if $errorType[termsConfirmed]|isset}
						<small class="innerError">
							{lang}wcf.global.form.error.empty{/lang}
						</small>
					{/if}
				</dd>
			</dl>
		{/if}

		{event name='generalFields'}
	</div>
		
	{foreach from=$optionTree item=category}
		{include file='userOptionFieldList' options=$category[options] langPrefix='wcf.user.option.'}
	{/foreach}
	
	{event name='sections'}

	{include file='shared_captcha' supportsAsyncCaptcha=true}
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.user.button.register{/lang}" accesskey="s">
		{csrfToken}
	</div>
	
	{include file='thirdPartySsoButtons'}
</form>

<p class="formFieldRequiredNotice">
	<span class="formFieldRequired">*</span>
	{lang}wcf.global.form.required{/lang}
</p>

<script data-relocate="true">
	require(['WoltLabSuite/Core/Controller/User/Registration'], ({ setup }) => {
		{jsphrase name='wcf.user.username.error.invalid'}
		{jsphrase name='wcf.user.username.error.notUnique'}
		{jsphrase name='wcf.user.email.error.invalid'}
		{jsphrase name='wcf.user.email.error.notUnique'}
		
		setup(
			document.getElementById('{@$randomFieldNames[username]}'),
			document.getElementById('{@$randomFieldNames[email]}'),
			document.getElementById('{@$randomFieldNames[password]}'),
			{
				minlength: {@REGISTER_USERNAME_MIN_LENGTH},
				maxlength: {@REGISTER_USERNAME_MAX_LENGTH}
			}
		);
	});
	require(['WoltLabSuite/Core/Ui/User/PasswordStrength', 'Language'], (PasswordStrength, Language) => {
		{include file='shared_passwordStrengthLanguage'}
		
		new PasswordStrength(document.getElementById('{@$randomFieldNames[password]}'), {
			relatedInputs: [
				document.getElementById('{@$randomFieldNames[username]}'),
				document.getElementById('{@$randomFieldNames[email]}')
			]
		});
	});
</script>

{include file='authFlowFooter'}
