{if $errorField}
	{if ($errorField|is_array && $errorField[__securityToken]|isset) || $errorField == '__securityToken'}
		<p class="error">{lang}wcf.global.form.error.securityToken{/lang}</p>
	{/if}
{/if}