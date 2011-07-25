{include file='header'}

<h2>{lang}wcf.global.next{/lang}</h2>

<p>{lang}wcf.global.next.description{/lang}</p>

<hr />

<form method="get" action="{@RELATIVE_WCF_DIR}acp/index.php">
	<div class="nextButton">
		<input type="submit" name="nextButton" accesskey="s" value="{lang}wcf.global.button.next{/lang}" />
		{@SID_INPUT_TAG}
	</div>
</form>

<script type="text/javascript">
	//<![CDATA[
	window.onload = function() {
		document.forms[0].nextButton.disabled = true;
		document.forms[0].submit();
	}
	//]]>
</script>

{include file='footer'}