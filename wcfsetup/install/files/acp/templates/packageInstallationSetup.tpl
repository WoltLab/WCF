{include file='header' templateName='packageInstallationSetup' templateNameApplication='wcf' __disableLoginLink=true __disableAds=true}

<style>
	#pageHeaderPanel,
	#pageFooter {
		pointer-events: none !important;
	}
</style>
<script data-relocate="true">
	{jsphrase name='wcf.acp.package.install.title'}

	require(['WoltLabSuite/Core/Acp/Component/Package/Installation'], ({ startInstallation }) => {
		void startInstallation({$queueID});
	});
</script>

<header class="contentHeader">
	<h1 class="contentTitle">{lang}wcf.acp.index.setup.title{/lang}</h1>
</header>

<p>{lang}wcf.acp.index.setup.notice{/lang}</p>

{include file='footer'}
