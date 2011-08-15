{capture assign='pageTitle'}{lang}wcf.acp.login{/lang}{/capture}
{include file='setupHeader'}

<script type="text/javascript">
	//<![CDATA[
	onloadEvents.push(function() { if (!'{$username|encodeJS}' || '{$errorField}' == 'username') document.getElementById('username').focus(); else document.getElementById('password').focus(); });
	//]]>
</script>

<header class="mainHeading setup">
	<img src="{@RELATIVE_WCF_DIR}icon/logIn1.svg" alt="" />
	<hgroup>
		<h1>{@$pageTitle}</h1>
	</hgroup>
</header>

{if $errorField != ''}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

<form method="post" action="index.php?form=Login">
	<fieldset>
		<legend>{lang}wcf.acp.login.data{/lang}</legend>
		
		<dl{if $errorField == 'username'} class="errorField"{/if}>
			<dt><label for="username">{lang}wcf.user.username{/lang}</label></dt>
			<dd><input type="text" id="username" name="username" value="{$username}" class="medium" />
				{if $errorField == 'username'}
					<small>
						{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
						{if $errorType == 'notFound'}{lang}wcf.user.error.username.notFound{/lang}{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
		<dl{if $errorField == 'password'} class="errorField"{/if}>
			<dt><label for="password">{lang}wcf.user.password{/lang}</label></dt>
			<dd><input type="password" id="password" name="password" value="" class="medium" />
				{if $errorField == 'password'}
					<small>
						<img src="{@RELATIVE_WCF_DIR}icon/errorS.png" alt="" />
						{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
						{if $errorType == 'false'}{lang}wcf.user.error.password.false{/lang}{/if}
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

{include file='setupFooter'}
