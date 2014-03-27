<div>
	<fieldset>
		<dl>
			<dt><label for="username">{lang}wcf.user.username{/lang}</label></dt>
			<dd>
				<input type="text" id="username" name="username" value="{$username}" required="required" class="long" autofocus="true" />
			</dd>
		</dl>
	</fieldset>
	
	{if MODULE_SYSTEM_RECAPTCHA}
		{include file='recaptcha'}
	{/if}
</div>

<div class="formSubmit">
	<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
</div>
