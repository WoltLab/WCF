{include file='header' templateName='packageInstallationSetup' templateNameApplication='wcf'}

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		WCF.Language.add('wcf.acp.package.install.title', '{lang}wcf.acp.package.install.title{/lang}');
		
		var $installation = new WCF.ACP.Package.Installation({@$queueID});
		$installation.prepareInstallation();
	});
	//]]>
</script>

<header class="contentHeader">
	<h1 class="contentTitle">{lang}wcf.acp.index.setup.title{/lang}</h1>
</header>

<p>{lang}wcf.acp.index.setup.notice{/lang}</p>

{include file='footer'}
