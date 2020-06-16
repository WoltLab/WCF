{if !$errorField|empty}
	{if ($errorField|is_array && $errorField[__securityToken]|isset) || $errorField == '__securityToken'}
		<p class="error" role="alert">{lang}wcf.global.form.error.securityToken{/lang}</p>
	{else}
		<p class="error" role="alert">{lang}wcf.global.form.error{/lang}</p>
	{/if}
{/if}
