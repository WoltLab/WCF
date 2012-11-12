{include file='header' templateName='packageInstallationSetup'}

<script type="text/javascript">
	//<![CDATA[
	$(function() {
		WCF.Language.add('wcf.acp.package.installation.title', '{lang}wcf.acp.package.installation.title{/lang}');

		var $installation = new WCF.ACP.Package.Installation({@$queueID});
		$installation.prepareInstallation();
	});
	//]]>
</script>

{include file='footer'}
