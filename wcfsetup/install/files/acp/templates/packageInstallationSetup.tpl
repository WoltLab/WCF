{include file='header' templateName='packageInstallationSetup'}

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		WCF.Language.add('wcf.acp.package.install.title', '{lang}wcf.acp.package.install.title{/lang}');
		
		var $installation = new WCF.ACP.Package.Installation({@$queueID});
		$installation.prepareInstallation();
	});
	//]]>
</script>

<header class="boxHeadline">
	<h1>{lang}wcf.acp.index.setup.title{/lang}</h1>
</header>

<p>{lang}wcf.acp.index.setup.notice{/lang}</p>

{include file='footer'}
