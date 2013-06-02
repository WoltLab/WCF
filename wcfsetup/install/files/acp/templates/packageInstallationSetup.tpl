{include file='header' templateName='packageInstallationSetup'}

<script type="text/javascript">
	//<![CDATA[
	$(function() {
		WCF.Language.add('wcf.acp.package.install.title', '{lang}wcf.acp.package.install.title{/lang}');

		var $installation = new WCF.ACP.Package.Installation({@$queueID});
		$installation.prepareInstallation();
	});
	//]]>
</script>

{include file='footer'}
