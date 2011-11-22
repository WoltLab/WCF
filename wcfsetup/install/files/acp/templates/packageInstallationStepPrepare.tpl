<div id="packageInstallationDialogContainer" class="overlay">
	<header class="mainHeading">
		<img src="{@RELATIVE_WCF_DIR}icon/working2.svg" alt="" />
		<hgroup>
			<h1>{lang}wcf.acp.package.installation.title{/lang}</h1>
			<h2>{lang}wcf.acp.package.installation.currentStep{/lang} <span id="packageInstallationAction">{lang}wcf.acp.package.installation.step.prepare{/lang}</span></h2>
			<p><progress id="packageInstallationProgress" value="0" max="100" style="width: 200px;">0%</progress></p>
		</hgroup>
	</header>
	
	<div id="packageInstallationInnerContentContainer" style="display: none;">
		<div id="packageInstallationInnerContent"></div>
	</div>
</div>
