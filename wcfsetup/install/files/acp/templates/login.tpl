{capture assign='pageTitle'}{lang}wcf.acp.login{/lang}{/capture}
{include file='setupHeader'}

<script type="text/javascript">
	//<![CDATA[
	onloadEvents.push(function() { if (!'{$username|encodeJS}' || '{$errorField}' == 'username') document.getElementById('username').focus(); else document.getElementById('password').focus(); });
	//]]>
</script>

<img class="icon" src="{@RELATIVE_WCF_DIR}icon/loginXL.png" alt="" />

<h1>{@$pageTitle}</h1>

<hr />

{if $errorField != ''}
<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

<form method="post" action="index.php?form=Login">
	<fieldset>
		<legend>{lang}wcf.acp.login.data{/lang}</legend>
		
		<div class="inner">
			<div{if $errorField == 'username'} class="errorField"{/if}>
				<label for="username">{lang}wcf.user.username{/lang}</label>
				<input type="text" class="inputText" id="username" name="username" value="{$username}" />
				{if $errorField == 'username'}
					<p>
						<img src="{@RELATIVE_WCF_DIR}icon/errorS.png" alt="" />
						{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
						{if $errorType == 'notFound'}{lang}wcf.user.error.username.notFound{/lang}{/if}
					</p>
				{/if}
			</div>
			
			<div{if $errorField == 'password'} class="errorField"{/if}>
				<label for="password">{lang}wcf.user.password{/lang}</label>
				<input type="password" class="inputText" id="password" name="password" value="" />
				{if $errorField == 'password'}
					<p>
						<img src="{@RELATIVE_WCF_DIR}icon/errorS.png" alt="" />
						{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
						{if $errorType == 'false'}{lang}wcf.user.error.password.false{/lang}{/if}
					</p>
				{/if}
			</div>
			
			{if $additionalFields|isset}{@$additionalFields}{/if}
		</div>
	</fieldset>
	
	<div class="nextButton">
		<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
		<input type="hidden" name="url" value="{$url}" />
 		{@SID_INPUT_TAG}
	</div>
</form>

{include file='setupFooter'}
