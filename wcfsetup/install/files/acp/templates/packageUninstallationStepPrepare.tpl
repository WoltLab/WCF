<div id="packageInstallationDialogContainer" class="overlay">
	<header class="mainHeading">
		<img src="{@RELATIVE_WCF_DIR}icon/working2.svg" alt="" />
		<hgroup>
			<h1>Paket &raquo;{$queue->packageName}&laquo; wird deinstalliert &hellip;</h1>
			<h2>Aktueller Schritt: <span id="packageInstallationAction">{lang}wcf.package.uninstallation.step.prepare{/lang}</span></h2>
			<p><progress id="packageInstallationProgress" value="0" max="100">0%</progress></p>
		</hgroup>
	</header>
	
	<div id="packageInstallationInnerContentContainer" style="display: none;">
		<div id="packageInstallationInnerContent"></div>
	</div>
</div>