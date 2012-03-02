<div id="packageInstallationDialogContainer">
	<header class="wcf-container wcf-mainHeading">
		<img src="{@$__wcf->getPath()}icon/working2.svg" alt="" class="wcf-containerIcon" />
		<hgroup class="wcf-containerContent">
			<h1>{lang}wcf.acp.package.installation.title{/lang}</h1>
			<h2 id="packageInstallationAction">{lang}wcf.acp.package.installation.step.prepare{/lang}</span></h2>
			<p><progress id="packageInstallationProgress" value="0" max="100">0%</progress> <span id="packageInstallationProgressLabel">0%</span></p>
		</hgroup>
	</header>
	
	<div id="packageInstallationInnerContentContainer" style="display: none;">
		<div id="packageInstallationInnerContent"></div>
	</div>
</div>
