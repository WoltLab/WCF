{include file='header'}

<h2>{lang}wcf.global.createUser{/lang}</h2>

<p>{lang}wcf.global.createUser.description{/lang}</p>

{if $errorField}
<p class="error">{lang}wcf.global.createUser.error{/lang}</p>
{/if}

<form method="post" action="install.php">
	<fieldset>
		<legend>{lang}wcf.global.createUser.data{/lang}</legend>
		
		<div class="inner">
			<div{if $errorField == 'username'} class="errorField"{/if}>
				<label for="username">{lang}wcf.global.createUser.username{/lang}</label>
				<input type="text" class="inputText" id="username" name="username" value="{$username}" style="width: 100%;" />
				{if $errorField == 'username'}
					<p>
						{if $errorType == 'empty'}{lang}wcf.global.createUser.error.empty{/lang}{/if}
						{if $errorType == 'notValid'}{lang}wcf.global.createUser.error.username.notValid{/lang}{/if}
					</p>
				{/if}
			</div>
			
			<div{if $errorField == 'email'} class="errorField"{/if}>
				<label for="email">{lang}wcf.global.createUser.email{/lang}</label>
				<input type="text" class="inputText" id="email" name="email" value="{$email}" style="width: 100%;" />
				{if $errorField == 'email'}
					<p>
						{if $errorType == 'empty'}{lang}wcf.global.createUser.error.empty{/lang}{/if}
						{if $errorType == 'notValid'}{lang}wcf.global.createUser.error.email.notValid{/lang}{/if}
					</p>
				{/if}
			</div>
			
			<div{if $errorField == 'confirmEmail'} class="errorField"{/if}>
				<label for="confirmEmail">{lang}wcf.global.createUser.confirmEmail{/lang}</label>
				<input type="text" class="inputText" id="confirmEmail" name="confirmEmail" value="{$confirmEmail}" style="width: 100%;" />
				{if $errorField == 'confirmEmail'}
					<p>
						{if $errorType == 'notEqual'}{lang}wcf.global.createUser.error.confirmEmail.notEqual{/lang}{/if}
					</p>
				{/if}
			</div>
			
			<div{if $errorField == 'password'} class="errorField"{/if}>
				<label for="password">{lang}wcf.global.createUser.password{/lang}</label>
				<input type="password" class="inputText" id="password" name="password" value="{$password}" style="width: 100%;" />
				{if $errorField == 'password'}
					<p>
						{if $errorType == 'empty'}{lang}wcf.global.createUser.error.empty{/lang}{/if}
					</p>
				{/if}
			</div>
			
			<div{if $errorField == 'confirmPassword'} class="errorField"{/if}>
				<label for="confirmPassword">{lang}wcf.global.createUser.confirmPassword{/lang}</label>
				<input type="password" class="inputText" id="confirmPassword" name="confirmPassword" value="{$confirmPassword}" style="width: 100%;" />
				{if $errorField == 'confirmPassword'}
					<p>
						{if $errorType == 'notEqual'}{lang}wcf.global.createUser.error.confirmPassword.notEqual{/lang}{/if}
					</p>
				{/if}
			</div>
			
		</div>
	</fieldset>
	
	<div class="nextButton">
		<input type="submit" accesskey="s" value="{lang}wcf.global.button.next{/lang}" />
		<input type="hidden" name="send" value="1" />
		<input type="hidden" name="step" value="{@$nextStep}" />
		<input type="hidden" name="tmpFilePrefix" value="{@$tmpFilePrefix}" />
		<input type="hidden" name="languageCode" value="{@$languageCode}" />
		<input type="hidden" name="wcfDir" value="{$wcfDir}" />
		<input type="hidden" name="dev" value="{@$developerMode}" />
	</div>
</form>

{include file='footer'}