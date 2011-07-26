<div id="packageInstallationDialogContainer">
	<header class="mainHeading">
		<img src="{@RELATIVE_WCF_DIR}icon/packageInstallL.png" alt="" />
		<hgroup>
			<h1>Paket &raquo;{$queue->packageName}&laquo; wird installiert &hellip;</h1>
			<h2>Aktueller Schritt: <span id="packageInstallationAction">{lang}wcf.package.installation.step.prepare{/lang}</span></h2>
			<p><progress id="packageInstallationProgress" value="0" max="100" style="width: 200px;">0%</progress></p>
		</hgroup>
	</header>
	
	<div id="packageInstallationInnerContentContainer" class="border content" style="display: none;">
		<div id="packageInstallationInnerContent" class="container-1"></div>
	</div>
</div>