{include file='header' templateName='packageInstallationSetup' templateNameApplication='wcf' __disableLoginLink=true __disableAds=true}

<style>
	#pageHeaderPanel,
	#pageFooter {
		pointer-events: none !important;
	}
</style>
<script data-relocate="true">
	$(function() {
		WCF.Language.add('wcf.acp.package.install.title', '{jslang}wcf.acp.package.install.title{/jslang}');
		
		var $installation = new WCF.ACP.Package.Installation({@$queueID});
		$installation.prepareInstallation();
	});
</script>

<header class="contentHeader">
	<h1 class="contentTitle">{lang}wcf.acp.index.setup.title{/lang}</h1>
</header>

<p>{lang}wcf.acp.index.setup.notice{/lang}</p>

{include file='footer'}
