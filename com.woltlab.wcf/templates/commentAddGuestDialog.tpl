<dl{if $errorType[username]|isset} class="formError"{/if}>
	<dt><label for="username">{lang}wcf.user.username{/lang}</label></dt>
	<dd>
		<input type="text" id="username" name="username" value="{$username}" required class="long" autofocus="true">
		{if $errorType[username]|isset}
			<small class="innerError">
				{if $errorType[username] == 'empty'}
					{lang}wcf.global.form.error.empty{/lang}
				{else}
					{lang}wcf.user.username.error.{$errorType[username]}{/lang}
				{/if}
			</small>
		{/if}
	</dd>
</dl>

{include file='shared_captcha'}
