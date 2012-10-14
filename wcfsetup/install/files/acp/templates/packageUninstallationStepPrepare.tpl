<div id="packageInstallationDialogContainer">
	<header class="box48 boxHeadline">
		<img src="{@$__wcf->getPath()}icon/working.svg" alt="" class="icon48" />
		<hgroup>
			<h1>Paket &raquo;{$queue->packageName}&laquo; wird deinstalliert &hellip;</h1><!-- ToDo: Language variables -->
			<h2>Aktueller Schritt: <span id="packageInstallationAction">{lang}wcf.package.uninstallation.step.prepare{/lang}</span></h2>
			<p><progress id="packageInstallationProgress" value="0" max="100">0%</progress></p>
		</hgroup>
	</header>
	
	<div id="packageInstallationInnerContentContainer" style="display: none;">
		<div id="packageInstallationInnerContent"></div>
	</div>
</div>