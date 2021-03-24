<div class="section">
	<dl>
		<dt>{lang}wcf.moderation.assignedUser{/lang}</dt>
		<dd>
			<ul>
				{if $assignedUser && $assignedUser->userID != $__wcf->getUser()->userID}
					<li><label><input type="radio" name="assignedUserID" value="{@$assignedUser->userID}" checked> {$assignedUser->username}</label></li>
				{/if}
				<li><label><input type="radio" name="assignedUserID" value="{@$__wcf->getUser()->userID}"{if $assignedUser && $assignedUser->userID == $__wcf->getUser()->userID} checked{/if}> {$__wcf->getUser()->username}</label></li>
				<li><label><input type="radio" name="assignedUserID" value="0"{if !$assignedUser} checked{/if}> {lang}wcf.moderation.assignedUser.nobody{/lang}</label></li>
				<li>
					<input type="radio" name="assignedUserID" value="-1"{if !$assignedUser} checked{/if}>
					<input type="text" id="assignedUsername" name="assignedUsername" value="{if $assignedUser && $assignedUser->userID != $__wcf->getUser()->userID}{$assignedUser->username}{/if}">
					{*if $errorField == 'assignedUsername'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{elseif $errorType == 'notAffected'}
								{lang}wcf.moderation.assignedUser.error.{@$errorType}{/lang}
							{else}
								{lang username=$assignedUsername}wcf.user.username.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if*}
				</li>
			</ul>
		</dd>
	</dl>
	
	<div class="formSubmit">
		<button data-type="submit">{lang}wcf.global.button.submit{/lang}</button>
	</div>
</div>

<script data-relocate="true">
	require(['WoltLabSuite/Core/Ui/User/Search/Input'], (UiUserSearchInput) => {
		const username = document.getElementById('assignedUsername');
		new UiUserSearchInput(username);
		
		username.addEventListener('click', (event) => {
			event.currentTarget.closest('li').querySelector('input[type=radio]').click();
		});
	});
</script>
