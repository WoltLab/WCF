{include file='authFlowHeader'}

<woltlab-core-notice type="info">{lang}wcf.user.newPassword.info{/lang}</woltlab-core-notice>

{@$form->getHtml()}

<script data-relocate="true">
	require(['WoltLabSuite/Core/Ui/User/PasswordStrength', 'Language'], (PasswordStrength, Language) => {
		{include file='shared_passwordStrengthLanguage'}
		
		new PasswordStrength(document.getElementById('newPassword'), {
			staticDictionary: [
				'{$user->username|encodeJS}',
				'{$user->email|encodeJS}',
			]
		});
	})
</script>

{include file='authFlowFooter'}
