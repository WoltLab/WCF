{include file='header'}

{if $errorField}
	<p class="error">{lang}wcf.global.createUser.error{/lang}</p>
{/if}

<form method="post" action="install.php">
	<section class="section">
		<header class="sectionHeader">
			<h2 class="sectionTitle">{lang}wcf.global.createUser{/lang}</h2>
			<p class="sectionDescription">{lang}wcf.global.createUser.description{/lang}</p>
		</header>
	
		<dl{if $errorField == 'username'} class="formError"{/if}>
			<dt><label for="username">{lang}wcf.global.createUser.username{/lang}</label></dt>
			<dd>
				<input type="text" id="username" name="username" value="{$username}" required class="medium">
				{if $errorField == 'username'}
					<small class="innerError">
						{if $errorType == 'empty'}{lang}wcf.global.createUser.error.empty{/lang}{/if}
						{if $errorType == 'invalid'}{lang}wcf.global.createUser.error.username.invalid{/lang}{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
		<dl{if $errorField == 'email'} class="formError"{/if}>
			<dt><label for="email">{lang}wcf.global.createUser.email{/lang}</label></dt>
			<dd>
				<input type="email" id="email" name="email" value="{$email}" required class="medium">
				{if $errorField == 'email'}
					<small class="innerError">
						{if $errorType == 'empty'}{lang}wcf.global.createUser.error.empty{/lang}{/if}
						{if $errorType == 'invalid'}{lang}wcf.global.createUser.error.email.invalid{/lang}{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
		<dl{if $errorField == 'confirmEmail'} class="formError"{/if}>
			<dt><label for="confirmEmail">{lang}wcf.global.createUser.confirmEmail{/lang}</label></dt>
			<dd>
				<input type="email" id="confirmEmail" name="confirmEmail" value="{$confirmEmail}" required class="medium">
				{if $errorField == 'confirmEmail'}
					<small class="innerError">
						{if $errorType == 'notEqual'}{lang}wcf.global.createUser.error.confirmEmail.notEqual{/lang}{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
		<dl{if $errorField == 'password'} class="formError"{/if}>
			<dt><label for="password">{lang}wcf.global.createUser.password{/lang}</label></dt>
			<dd>
				<input type="password" id="password" name="password" value="{$password}" required class="medium">
				{if $errorField == 'password'}
					<small class="innerError">
						{if $errorType == 'empty'}{lang}wcf.global.createUser.error.empty{/lang}{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
		<dl{if $errorField == 'confirmPassword'} class="formError"{/if}>
			<dt><label for="confirmPassword">{lang}wcf.global.createUser.confirmPassword{/lang}</label></dt>
			<dd>
				<input type="password" id="confirmPassword" name="confirmPassword" value="{$confirmPassword}" required class="medium">
				{if $errorField == 'confirmPassword'}
					<small class="innerError">
						{if $errorType == 'notEqual'}{lang}wcf.global.createUser.error.confirmPassword.notEqual{/lang}{/if}
					</small>
				{/if}
			</dd>
		</dl>
	</section>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.next{/lang}" accesskey="s">
		<input type="hidden" name="send" value="1">
		<input type="hidden" name="step" value="{$nextStep}">
		<input type="hidden" name="tmpFilePrefix" value="{$tmpFilePrefix}">
		<input type="hidden" name="languageCode" value="{$languageCode}">
		<input type="hidden" name="dev" value="{$developerMode}">
	</div>
</form>
<script>
if (typeof window._trackWcfSetupStep === 'function') window._trackWcfSetupStep('createUser');
</script>
{include file='footer'}
