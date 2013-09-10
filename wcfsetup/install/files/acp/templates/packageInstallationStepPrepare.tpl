<div id="packageInstallationDialogContainer">
	<header class="box48 boxHeadline">
		<span class="icon icon48 icon-spinner jsPackageInstallationStatus"></span>
		
		<div>
			<h1>{lang}wcf.acp.package.{@$installationType}.title{/lang}</h1>
			<p id="packageInstallationAction">{lang}wcf.acp.package.{@$installationType}.step.prepare{/lang}</span></p>
			<small><progress id="packageInstallationProgress" value="0" max="100">0%</progress> <span id="packageInstallationProgressLabel">0%</span></small>
		</div>
	</header>
	
	<div id="packageInstallationInnerContentContainer" style="display: none;">
		<div id="packageInstallationInnerContent"></div>
	</div>
</div>
