{include file='header' pageTitle='wcf.user.reauthentication' __isLogin=true}

{*
<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.user.reauthentication{/lang}</h1>
	</div>
</header>

<p class="info" role="status">{lang}wcf.user.reauthentication.explanation{/lang}</p>
*}

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
					document.getElementById("password").focus();
				}, 2);
			}
		});
	});
</script>

{include file='footer'}
