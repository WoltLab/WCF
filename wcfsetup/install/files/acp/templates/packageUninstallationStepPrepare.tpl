<div id="packageInstallationDialogContainer">
	<header class="mainHeading">
		<img src="{@RELATIVE_WCF_DIR}icon/packageUninstallL.png" alt="" />
		<hgroup>
			<h1>Paket &raquo;{$queue->packageName}&laquo; wird deinstalliert &hellip;</h1>
			<h2>Aktueller Schritt: <span id="packageInstallationAction">{lang}wcf.package.uninstallation.step.prepare{/lang}</span></h2>
			<p><progress value="0" max="100" style="width: 200px;" id="packageInstallationProgress">0%</progress></p>
		</hgroup>
	</header>
	
	<div class="border content" id="packageInstallationInnerContentContainer" style="display: none;">
		<div class="container-1" id="packageInstallationInnerContent"></div>
	</div>
</div>