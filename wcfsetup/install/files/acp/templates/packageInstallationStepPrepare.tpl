<div id="packageInstallationDialogContainer">
	<div class="mainHeadline">
		<img src="{@RELATIVE_WCF_DIR}icon/packageInstallL.png" alt="" />
		<div class="headlineContainer">
			<h2>Paket &raquo;{$queue->packageName}&laquo; wird installiert &hellip;</h2>
			<p>Aktueller Schritt: <span id="packageInstallationAction">{lang}wcf.package.installation.step.prepare{/lang}</span></p>
			<p><progress value="0" max="100" style="width: 200px;" id="packageInstallationProgress">0%</progress></p>
		</div>
	</div>
	
	
	<div class="border content" id="packageInstallationInnerContentContainer" style="display: none;">
		<div class="container-1" id="packageInstallationInnerContent"></div>
	</div>
</div>