<dl>
	<dt>{lang}wcf.user.reauthentication.loginAs{/lang}</dt>
	<dd>
		{$__wcf->user->username}

		{* This field is required to assist password managers. *}
		<input type="text" autocomplete="username" value="{$__wcf->user->username}" style="display: none">
	</dd>
</dl>
