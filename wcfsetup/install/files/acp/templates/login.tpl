{capture assign='pageTitle'}{lang}wcf.user.login{/lang}{/capture}
{include file='header'}

<script type="text/javascript">
	//<![CDATA[
	$(function() {
		if (!$('#username').val() || '{$errorField}' == 'username') {
			$('#username').focus();
		}
		else {
			$('#password').focus();
		}
	});
	//]]>
</script>

<header class="mainHeading setup">
	<img src="{@RELATIVE_WCF_DIR}icon/logIn1.svg" alt="" />
	<hgroup>
		<h1>{@$pageTitle}</h1>
	</hgroup>
</header>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

<form method="post" action="{link controller='Login'}{/link}">
	<fieldset>
		<legend>{lang}wcf.user.login.data{/lang}</legend>
		
		<dl{if $errorField == 'username'} class="formError"{/if}>
			<dt><label for="username">{lang}wcf.user.username{/lang}</label></dt>
			<dd><input type="text" id="username" name="username" value="{$username}" class="medium" />
				{if $errorField == 'username'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}wcf.user.username.error.{@$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
		<dl{if $errorField == 'password'} class="formError"{/if}>
			<dt><label for="password">{lang}wcf.user.password{/lang}</label></dt>
			<dd><input type="password" id="password" name="password" value="" class="medium" />
				{if $errorField == 'password'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}wcf.user.password.error.{@$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
		{if $additionalFields|isset}{@$additionalFields}{/if}
	</fieldset>
	
	<div class="formSubmit">
		<input type="reset" value="{lang}wcf.global.button.reset{/lang}" accesskey="r" />
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		<input type="hidden" name="url" value="{$url}" />
 		{@SID_INPUT_TAG}
	</div>
</form>

{include file='footer'}
