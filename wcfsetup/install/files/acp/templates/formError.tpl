{if !$errorField|empty}
	{if ($errorField|is_array && $errorField[__securityToken]|isset) || $errorField == '__securityToken'}
		<woltlab-core-notice type="error">{lang}wcf.global.form.error.securityToken{/lang}</woltlab-core-notice>
	{else}
		<woltlab-core-notice type="error">{lang}wcf.global.form.error{/lang}</woltlab-core-notice>
	{/if}
{/if}
