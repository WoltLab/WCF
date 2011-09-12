{include file='header'}

<hgroup class="subHeading">
	<h1>{lang}wcf.global.createUser{/lang}</h1>
	<h2>{lang}wcf.global.createUser.description{/lang}</h2>
</hgroup>

{if $errorField}
	<p class="error">{lang}wcf.global.createUser.error{/lang}</p>
{/if}

<form method="post" action="install.php">
	<fieldset>
		<legend>{lang}wcf.global.createUser.data{/lang}</legend>
		
		<dl{if $errorField == 'username'} class="errorField"{/if}>
			<dt><label for="username">{lang}wcf.global.createUser.username{/lang}</label></dt>
			<dd>
				<input type="text" id="username" name="username" value="{$username}" class="medium" />
				{if $errorField == 'username'}
					<small>
						{if $errorType == 'empty'}{lang}wcf.global.createUser.error.empty{/lang}{/if}
						{if $errorType == 'notValid'}{lang}wcf.global.createUser.error.username.notValid{/lang}{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
		<dl{if $errorField == 'email'} class="errorField"{/if}>
			<dt><label for="email">{lang}wcf.global.createUser.email{/lang}</label></dt>
			<dd>
				<input type="text" id="email" name="email" value="{$email}" class="medium" />
				{if $errorField == 'email'}
					<small>
						{if $errorType == 'empty'}{lang}wcf.global.createUser.error.empty{/lang}{/if}
						{if $errorType == 'notValid'}{lang}wcf.global.createUser.error.email.notValid{/lang}{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
		<dl{if $errorField == 'confirmEmail'} class="errorField"{/if}>
			<dt><label for="confirmEmail">{lang}wcf.global.createUser.confirmEmail{/lang}</label></dt>
			<dd>
				<input type="text" id="confirmEmail" name="confirmEmail" value="{$confirmEmail}" class="medium" />
				{if $errorField == 'confirmEmail'}
					<small>
						{if $errorType == 'notEqual'}{lang}wcf.global.createUser.error.confirmEmail.notEqual{/lang}{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
		<dl{if $errorField == 'password'} class="errorField"{/if}>
			<dt><label for="password">{lang}wcf.global.createUser.password{/lang}</label></dt>
			<dd>
				<input type="password" id="password" name="password" value="{$password}" class="medium" />
				{if $errorField == 'password'}
					<small>
						{if $errorType == 'empty'}{lang}wcf.global.createUser.error.empty{/lang}{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
		<dl{if $errorField == 'confirmPassword'} class="errorField"{/if}>
			<dt><label for="confirmPassword">{lang}wcf.global.createUser.confirmPassword{/lang}</label></dt>
			<dd>
				<input type="password" id="confirmPassword" name="confirmPassword" value="{$confirmPassword}" class="medium" />
				{if $errorField == 'confirmPassword'}
					<small>
						{if $errorType == 'notEqual'}{lang}wcf.global.createUser.error.confirmPassword.notEqual{/lang}{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
	</fieldset>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.next{/lang}" accesskey="s" />
		<input type="hidden" name="send" value="1" />
		<input type="hidden" name="step" value="{@$nextStep}" />
		<input type="hidden" name="tmpFilePrefix" value="{@$tmpFilePrefix}" />
		<input type="hidden" name="languageCode" value="{@$languageCode}" />
		<input type="hidden" name="wcfDir" value="{$wcfDir}" />
		<input type="hidden" name="dev" value="{@$developerMode}" />
	</div>
</form>

{include file='footer'}
