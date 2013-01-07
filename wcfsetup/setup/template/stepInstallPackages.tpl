{assign var=lastStep value=true}
{include file='header'}

<header class="boxHeadline boxSubHeadline">
	<hgroup>
		<h1>{lang}wcf.global.next{/lang}</h1>
		<h2>{lang}wcf.global.next.description{/lang}</h2>
	</hgroup>
</header>

<form method="get" action="{@RELATIVE_WCF_DIR}acp/index.php">
	<div class="formSubmit">
		{@SID_INPUT_TAG}
		<input type="hidden" name="action" value="WCFSetup" />
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
