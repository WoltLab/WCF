{include file='header' pageTitle='wcf.page.error.permissionDenied.title' templateName='acpNotAuthorized' templateNameApplication='wcf' __isLogin=true}

<div id="acpNotAuthorized" style="display: none">
	{include file='__reauthenticationLoginAs'}
	<woltlab-core-notice type="error">{lang}wcf.user.username.error.acpNotAuthorized{/lang}</woltlab-core-notice>
</div>

<script data-relocate="true">
	require(["WoltLabSuite/Core/Ui/Dialog"], (UiDialog) => {
		UiDialog.openStatic("acpNotAuthorized", null, {
			closable: false,
			title: '{lang}wcf.page.error.permissionDenied.title{/lang}',
		});
	});
</script>

{include file='footer'}
