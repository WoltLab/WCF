{assign var=lastStep value=true}
{include file='header'}

<hgroup class="subHeading">
	<h1>{lang}wcf.global.next{/lang}</h1>
	<h2>{lang}wcf.global.next.description{/lang}</h2>
</hgroup>

<form method="get" action="{@RELATIVE_WCF_DIR}acp/index.php">
	<div class="formSubmit">
		{@SID_INPUT_TAG}
	</div>
</form>

<script type="text/javascript">
	//<![CDATA[
	window.onload = function() {
		document.forms[0].submit();
	}
	//]]>
</script>

{include file='footer'}
