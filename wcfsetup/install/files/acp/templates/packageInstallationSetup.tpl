{include file='header' templateName='packageInstallationSetup'}

<script type="text/javascript">
	//<![CDATA[
	$installation = new WCF.ACP.PackageInstallation('install', {@$queueID}, false);
	$installation.prepareInstallation();
	//]]>
</script>

{include file='footer'}