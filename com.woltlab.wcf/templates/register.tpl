{capture assign='headContent'}
	<style type="text/css">
		#fieldset1 {
			display: none;
		}
	</style>
{/capture}

{include file='header' __disableLoginLink=true __disableAds=true}

{if $isExternalAuthentication}
	<p class="info" role="status">{lang}wcf.user.3rdparty.{$__wcf->session->getVar('__3rdPartyProvider')}.register{/lang}</p>
{/if}

{include file='formError'}

<form method="post" action="{link controller='Register'}{/link}">
	<div class="section">
		<dl{if $errorType[username]|isset} class="formError"{/if}>
			<dt>
				<label for="{@$randomFieldNames[username]}">{lang}wcf.user.username{/lang}</label> <span class="customOptionRequired">*</span>
			</dt>
			<dd>
				<input type="text" id="{@$randomFieldNames[username]}" name="{@$randomFieldNames[username]}" value="{$username}" required class="medium" autocomplete="username">
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
		
		{event name='usernameFields'}
	</div>
	
	<section class="section" id="fieldset1">
		<header class="sectionHeader">
			<h2 class="sectionTitle">{lang}wcf.user.register.honeyPot{/lang}</h2>
			<p class="sectionDescription">{lang}wcf.user.register.honeyPot.description{/lang}</p>
		</header>
		
		<dl>
			<dt>
				<label for="username">{lang}wcf.user.username{/lang}</label> <span class="customOptionRequired">*</span>
			</dt>
			<dd>
				<input type="text" id="username" name="username" value="" autocomplete="off" class="medium" tabindex="998">
			</dd>
		</dl>
		
		<dl>
			<dt>
				<label for="email">{lang}wcf.user.email{/lang}</label> <span class="customOptionRequired">*</span>
			</dt>
			<dd>
				<input type="email" id="email" name="email" value="" autocomplete="off" class="medium" tabindex="999">
			</dd>
		</dl>
		
		{event name='honeyPotFields'}
	</section>
	
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.user.email{/lang}</h2>
		
		<dl{if $errorType[email]|isset} class="formError"{/if}>
			<dt>
				<label for="{@$randomFieldNames[email]}">{lang}wcf.user.email{/lang}</label> <span class="customOptionRequired">*</span>
			</dt>
			<dd>
				<input type="email" id="{@$randomFieldNames[email]}" name="{@$randomFieldNames[email]}" value="{$email}" required class="medium">
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
		
		<dl{if $errorType[confirmEmail]|isset} class="formError"{/if}>
			<dt>
				<label for="{@$randomFieldNames[confirmEmail]}">{lang}wcf.user.confirmEmail{/lang}</label> <span class="customOptionRequired">*</span>
			</dt>
			<dd>
				<input type="email" id="{@$randomFieldNames[confirmEmail]}" name="{@$randomFieldNames[confirmEmail]}" value="{$confirmEmail}" required class="medium">
				{if $errorType[confirmEmail]|isset}
					<small class="innerError">
						{lang}wcf.user.confirmEmail.error.{$errorType[confirmEmail]}{/lang}
					</small>
				{/if}
			</dd>
		</dl>
		
		{event name='emailFields'}
	</section>
	
	{if !$isExternalAuthentication}
		<section class="section">
			<h2 class="sectionTitle">{lang}wcf.user.password{/lang}</h2>
			
			<dl{if $errorType[password]|isset} class="formError"{/if}>
				<dt>
					<label for="{@$randomFieldNames[password]}">{lang}wcf.user.password{/lang}</label> <span class="customOptionRequired">*</span>
				</dt>
				<dd>
					<input type="password" id="{@$randomFieldNames[password]}" name="{@$randomFieldNames[password]}" value="{$password}" required class="medium" autocomplete="new-password" passwordrules="{$passwordRulesAttributeValue}">
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
			
			<dl{if $errorType[confirmPassword]|isset} class="formError"{/if}>
				<dt>
					<label for="{@$randomFieldNames[confirmPassword]}">{lang}wcf.user.confirmPassword{/lang}</label> <span class="customOptionRequired">*</span>
				</dt>
				<dd>
					<input type="password" id="{@$randomFieldNames[confirmPassword]}" name="{@$randomFieldNames[confirmPassword]}" value="{$confirmPassword}" required class="medium" autocomplete="new-password" passwordrules="{$passwordRulesAttributeValue}">
					{if $errorType[confirmPassword]|isset}
						<small class="innerError">
							{lang}wcf.user.confirmPassword.error.{$errorType[confirmPassword]}{/lang}
						</small>
					{/if}
				</dd>
			</dl>
			
			{event name='passwordFields'}
		</section>
	{/if}
	
	{if $availableLanguages|count > 1}
		<section class="section">
			<h2 class="sectionTitle">{lang}wcf.user.language{/lang}</h2>
			
			<dl>
				<dt><label for="languageID">{lang}wcf.user.language{/lang}</label></dt>
				<dd id="languageIDContainer">
					<script data-relocate="true">
						$(function() {
							var $languages = {
								{implode from=$availableLanguages item=language}
								'{@$language->languageID}': {
									iconPath: '{@$language->getIconPath()|encodeJS}',
									languageName: '{@$language|encodeJS}'
								}
								{/implode}
							};
							
							require(['WoltLabSuite/Core/Language/Chooser'], function(LanguageChooser) {
								LanguageChooser.init('languageIDContainer', 'languageID', {@$languageID}, $languages);
								
								var small = elCreate('small');
								small.innerHTML = '{lang}wcf.user.language.description{/lang}';
								elById('languageIDContainer').appendChild(small);
							});
						});
					</script>
					<noscript>
						<select name="languageID" id="languageID">
							{foreach from=$availableLanguages item=language}
								<option value="{@$language->languageID}"{if $language->languageID == $languageID} selected{/if}>{$language}</option>
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
							<label><input name="visibleLanguages[]" type="checkbox" value="{@$language->languageID}"{if $language->languageID|in_array:$visibleLanguages} checked{/if}> {$language}</label>
						{/foreach}
					{/content}
					<small>{lang}wcf.user.visibleLanguages.description{/lang}</small></dd>
				</dl>
			{/hascontent}
			
			{event name='languageFields'}
		</section>
	{/if}
	
	{foreach from=$optionTree item=category}
		<section class="section">
			<h2 class="sectionTitle">{lang}wcf.user.option.category.{@$category[object]->categoryName}{/lang}</h2>
			
			{include file='userOptionFieldList' options=$category[options] langPrefix='wcf.user.option.'}
		</section>
	{/foreach}
	
	{event name='sections'}
	
	{include file='captcha' supportsAsyncCaptcha=true}
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
	
	<div class="section">
		<p><span class="customOptionRequired">*</span> {lang}wcf.global.form.required{/lang}</p>
	</div>
</form>

<script data-relocate="true">
	$(function() {
		WCF.Language.addObject({
			'wcf.global.form.error.empty': '{lang}wcf.global.form.error.empty{/lang}',
			'wcf.user.username.error.invalid': '{lang}wcf.user.username.error.invalid{/lang}',
			'wcf.user.username.error.notUnique': '{lang}wcf.user.username.error.notUnique{/lang}',
			'wcf.user.email.error.invalid' : '{lang}wcf.user.email.error.invalid{/lang}',
			'wcf.user.email.error.notUnique' : '{lang}wcf.user.email.error.notUnique{/lang}',
			'wcf.user.confirmEmail.error.notEqual' : '{lang}wcf.user.confirmEmail.error.notEqual{/lang}',
			'wcf.user.password.error.notSecure' : '{lang}wcf.user.password.error.notSecure{/lang}',
			'wcf.user.confirmPassword.error.notEqual' : '{lang}wcf.user.confirmPassword.error.notEqual{/lang}'
		});
		
		new WCF.User.Registration.Validation.EmailAddress($('#{@$randomFieldNames[email]}'), $('#{@$randomFieldNames[confirmEmail]}'), null);
		new WCF.User.Registration.Validation.Password($('#{@$randomFieldNames[password]}'), $('#{@$randomFieldNames[confirmPassword]}'), null);
		new WCF.User.Registration.Validation.Username($('#{@$randomFieldNames[username]}'), null, {
			minlength: {@REGISTER_USERNAME_MIN_LENGTH},
			maxlength: {@REGISTER_USERNAME_MAX_LENGTH}
		});
		
		require(['WoltLabSuite/Core/Ui/User/PasswordStrength', 'Language'], function (PasswordStrength, Language) {
			{include file='passwordStrengthLanguage'}
			
			new PasswordStrength(elById('{@$randomFieldNames[password]}'), {
				relatedInputs: [
					elById('{@$randomFieldNames[username]}'),
					elById('{@$randomFieldNames[email]}')
				]
			});
		})
	});
</script>

{include file='footer' __disableAds=true}
