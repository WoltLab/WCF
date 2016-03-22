<dl>
	<dt></dt>
	<dd{if $errorField == $inputName} class="formError"{/if}>
		{htmlCheckboxes options=$availableUserGroups name=$inputName selected=$selectedUserGroupIDs}
		{if $errorField == $inputName}
			<small class="innerError">{lang}wcf.global.form.error.{$errorType}{/lang}</small>
		{/if}
	</dd>
</dl>
