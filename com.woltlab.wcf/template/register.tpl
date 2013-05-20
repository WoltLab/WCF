{include file='documentHeader'}

<head>
	<title>{lang}wcf.user.register{/lang} - {PAGE_TITLE|language}</title>
	{include file='headInclude'}
	
	<script type="text/javascript" src="{@$__wcf->getPath()}js/WCF.User{if !ENABLE_DEBUG_MODE}.min{/if}.js"></script>
	<script type="text/javascript">
		//<![CDATA[
		$(function() {
			WCF.Language.addObject({
				'wcf.global.form.error.empty': '{lang}wcf.global.form.error.empty{/lang}',
				'wcf.user.username.error.notValid': '{lang}wcf.user.username.error.notValid{/lang}',
				'wcf.user.username.error.notUnique': '{lang}wcf.user.username.error.notUnique{/lang}',
				'wcf.user.email.error.notValid' : '{lang}wcf.user.email.error.notValid{/lang}',
				'wcf.user.email.error.notUnique' : '{lang}wcf.user.email.error.notUnique{/lang}',
				'wcf.user.confirmEmail.error.notEqual' : '{lang}wcf.user.confirmEmail.error.notEqual{/lang}',
				'wcf.user.password.error.notSecure' : '{lang}wcf.user.password.error.notSecure{/lang}',
				'wcf.user.confirmPassword.error.notEqual' : '{lang}wcf.user.confirmPassword.error.notEqual{/lang}'
			});
			
			new WCF.User.Registration.Validation.EmailAddress($('#email'), $('#confirmEmail'), null);
			new WCF.User.Registration.Validation.Password($('#password'), $('#confirmPassword'), null);
			new WCF.User.Registration.Validation.Username($('#username', null, {
				minlength: {@REGISTER_USERNAME_MIN_LENGTH},
				maxlength: {@REGISTER_USERNAME_MAX_LENGTH}
			}));
		});
		//]]>
	</script>
</head>

<body id="tpl{$templateName|ucfirst}">
{include file='header' __disableLoginLink=true}

<header class="boxHeadline">
	<h1>{lang}wcf.user.register{/lang}</h1>
</header>

{include file='userNotice'}

{if $isExternalAuthentication}
	{if $__wcf->session->getVar('__githubToken')}
		<p class="info">{lang}wcf.user.3rdparty.github.register{/lang}</p>
	{elseif $__wcf->session->getVar('__twitterData')}
		<p class="info">{lang}wcf.user.3rdparty.twitter.register{/lang}</p>
	{elseif $__wcf->session->getVar('__facebookData')}
		<p class="info">{lang}wcf.user.3rdparty.facebook.register{/lang}</p>
	{elseif $__wcf->session->getVar('__googleData')}
		<p class="info">{lang}wcf.user.3rdparty.google.register{/lang}</p>
	{/if}
{/if}

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

<div class="contentNavigation">
	{hascontent}
		<nav>
			<ul>
				{content}
					{event name='contentNavigationButtons'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</div>

<form method="post" action="{link controller='Register'}{/link}">
	<div class="container containerPadding marginTop">
		<fieldset>
			<legend>{lang}wcf.user.username{/lang}</legend>
			
			<dl{if $errorType.username|isset} class="formError"{/if}>
				<dt>
					<label for="username">{lang}wcf.user.username{/lang}</label>
				</dt>
				<dd>
					<input type="text" id="username" name="username" value="{$username}" required="required" class="medium" />
					{if $errorType.username|isset}
						<small class="innerError">
							{if $errorType.username == 'empty'}{lang}wcf.global.form.error.empty{/lang}{/if}
							{if $errorType.username == 'notValid'}{lang}wcf.user.username.error.notValid{/lang}{/if}
							{if $errorType.username == 'notUnique'}{lang}wcf.user.username.error.notUnique{/lang}{/if}
						</small>
					{/if}
					<small>{lang}wcf.user.username.description{/lang}</small>
				</dd>
			</dl>
			
			{event name='usernameFields'}
		</fieldset>
		
		<fieldset>
			<legend>{lang}wcf.user.email{/lang}</legend>
			
			<dl{if $errorType.email|isset} class="formError"{/if}>
				<dt>
					<label for="email">{lang}wcf.user.email{/lang}</label>
				</dt>
				<dd>
					<input type="email" id="email" name="email" value="{$email}" required="required" class="medium" />
					{if $errorType.email|isset}
						<small class="innerError">
							{if $errorType.email == 'empty'}{lang}wcf.global.form.error.empty{/lang}{/if}
							{if $errorType.email == 'notValid'}{lang}wcf.user.email.error.notValid{/lang}{/if}
							{if $errorType.email == 'notUnique'}{lang}wcf.user.email.error.notUnique{/lang}{/if}
						</small>
					{/if}
				</dd>
			</dl>
			
			<dl{if $errorType.confirmEmail|isset} class="formError"{/if}>
				<dt>
					<label for="confirmEmail">{lang}wcf.user.confirmEmail{/lang}</label>
				</dt>
				<dd>
					<input type="email" id="confirmEmail" name="confirmEmail" value="{$confirmEmail}" required="required" class="medium" />
					{if $errorType.confirmEmail|isset}
						<small class="innerError">
							{if $errorType.confirmEmail == 'notEqual'}{lang}wcf.user.confirmEmail.error.notEqual{/lang}{/if}
						</small>
					{/if}
				</dd>
			</dl>
			
			{event name='emailFields'}
		</fieldset>
		
		{if !$isExternalAuthentication}
			<fieldset>
				<legend>{lang}wcf.user.password{/lang}</legend>
				
				<dl{if $errorType.password|isset} class="formError"{/if}>
					<dt>
						<label for="password">{lang}wcf.user.password{/lang}</label>
					</dt>
					<dd>
						<input type="password" id="password" name="password" value="{$password}" required="required" class="medium" />
						{if $errorType.password|isset}
							<small class="innerError">
								{if $errorType.password == 'empty'}{lang}wcf.global.form.error.empty{/lang}{/if}
								{if $errorType.password == 'notSecure'}{lang}wcf.user.password.error.notSecure{/lang}{/if}
							</small>
						{/if}
						<small>{lang}wcf.user.password.description{/lang}</small>
					</dd>
				</dl>
				
				<dl{if $errorType.confirmPassword|isset} class="formError"{/if}>
					<dt>
						<label for="confirmPassword">{lang}wcf.user.confirmPassword{/lang}</label>
					</dt>
					<dd>
						<input type="password" id="confirmPassword" name="confirmPassword" value="{$confirmPassword}" required="required" class="medium" />
						{if $errorType.confirmPassword|isset}
							<small class="innerError">
								{if $errorType.confirmPassword == 'notEqual'}{lang}wcf.user.confirmPassword.error.notEqual{/lang}{/if}
							</small>
						{/if}
					</dd>
				</dl>
				
				{event name='passwordFields'}
			</fieldset>
		{/if}
		
		{if $availableLanguages|count > 1}
			<fieldset>
				<legend>{lang}wcf.user.language{/lang}</legend>
				
				<dl>
					<dt><label for="languageID">{lang}wcf.user.language{/lang}</label></dt>
					<dd>
						<select id="languageID" name="languageID">
							{foreach from=$availableLanguages item=language}
								<option value="{@$language->languageID}"{if $language->languageID == $languageID} selected="selected"{/if}>{$language}</option>
							{/foreach}
						</select>
						<small>{lang}wcf.user.language.description{/lang}</small>
					</dd>
				</dl>
				
				{hascontent}
					<dl>
						<dt><label>{lang}wcf.user.visibleLanguages{/lang}</label></dt>
						<dd class="floated">
						{content}
							{foreach from=$availableContentLanguages item=language}
								<label><input name="visibleLanguages[]" type="checkbox" value="{@$language->languageID}"{if $language->languageID|in_array:$visibleLanguages} checked="checked"{/if} /> {$language}</label>
							{/foreach}
						{/content}
						<small>{lang}wcf.user.visibleLanguages.description{/lang}</small></dd>
					</dl>
				{/hascontent}
				
				{event name='languageFields'}
			</fieldset>
		{/if}
		
		{foreach from=$optionTree item=category}
			<fieldset>
				<legend>{lang}wcf.user.option.category.{@$category[object]->categoryName}{/lang}</legend>
				
				{include file='userOptionFieldList' options=$category[options] langPrefix='wcf.user.option.'}
			</fieldset>
		{/foreach}
		
		{event name='fieldsets'}
		
		{if $useCaptcha}
			{if $errorType.recaptchaString|isset}
				{assign var=errorField value='recaptchaString'}
				{assign var=errorType value=$errorType.recaptchaString}
			{/if}
			{include file='recaptcha'}
		{/if}
	</div>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
	</div>
</form>

{include file='footer'}

</body>
</html>