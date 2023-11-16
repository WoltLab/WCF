{include file='header' pageTitle='wcf.user.reauthentication'}

<div id="reauthentication" style="display: none">
	{@$form->getHtml()}
</div>

<script data-relocate="true">
	require(["WoltLabSuite/Core/Ui/Dialog"], (UiDialog) => {
		UiDialog.openStatic("reauthentication", null, {
			closable: false,
			title: '{lang}wcf.user.reauthentication{/lang}',
			onShow() {
				setTimeout(() => {
					document.getElementById("password")?.focus();
				}, 2);
			}
		});
	});
</script>

{include file='footer'}
