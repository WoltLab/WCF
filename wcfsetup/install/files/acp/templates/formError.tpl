{if !$errorField|empty}
	{if ($errorField|is_array && $errorField[__securityToken]|isset) || $errorField == '__securityToken'}
		<p class="error">{lang}wcf.global.form.error.securityToken{/lang}</p>
	{else}
		<p class="error">{lang}wcf.global.form.error{/lang}</p>
	{/if}
{/if}