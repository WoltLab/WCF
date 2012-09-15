{include file="documentHeader"}

<head>
	<title>{lang}wcf.global.error.permissionDenied.title{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
	
	{include file='headInclude'}
</head>

<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>

{include file='header'}
	
<p class="error">{lang}wcf.global.error.permissionDenied{/lang}</p>

{if !$__wcf->user->userID}<form method="post" action="{link controller='Login'}{/link}" class="loginForm">
	<div class="container containerPadding marginTop shadow">
		<fieldset>
			<legend>{lang}wcf.user.login.data{/lang}</legend>
			
			<dl>
				<dt><label for="username">{lang}wcf.user.usernameOrEmail{/lang}</label></dt>
				<dd>
					<input type="text" id="username" name="username" value="" required="required" autofocus="autofocus" class="long" />
				</dd>
			</dl>
			
			{if !REGISTER_DISABLED}
				<dl>
					<dt>{lang}wcf.user.login.action{/lang}</dt>
					<dd>
						<label><input type="radio" name="action" value="register" /> {lang}wcf.user.login.action.register{/lang}</label>
						<label><input type="radio" name="action" value="login" checked="checked" /> {lang}wcf.user.login.action.login{/lang}</label>
					</dd>
				</dl>
			{/if}
			
			<dl>
				<dt><label for="password">{lang}wcf.user.password{/lang}</label></dt>
				<dd>
					<input type="password" id="password" name="password" value="" required="required" class="long" />
				</dd>
			</dl>
			
			<dl>
				<dd><label><input type="checkbox" id="useCookies" name="useCookies" value="1" checked="checked" /> {lang}wcf.user.useCookies{/lang}</label></dd>
			</dl>
			
			{event name='fields'}
			
			<div class="formSubmit">
				<input type="submit" id="loginSubmitButton" name="submitButton" value="{lang}wcf.user.button.login{/lang}" accesskey="s" />
				<input type="hidden" name="url" value="{$__wcf->session->requestURI}" />
			</div>
		</fieldset>
		
		{event name='fieldsets'}
	</div>
</form>{/if}

{if ENABLE_DEBUG_MODE}
	<!-- 
	{$name} thrown in {$file} ({@$line})
	Stracktrace:
	{$stacktrace}
	-->
{/if}

{include file='footer'}

</body>
</html>