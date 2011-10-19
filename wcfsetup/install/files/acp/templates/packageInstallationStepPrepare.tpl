<div id="packageInstallationDialogContainer" class="border overlay">
	<header class="mainHeading">
		<img src="{@RELATIVE_WCF_DIR}icon/working1.svg" alt="" />
		<hgroup>
			<h1>Paket &raquo;{$packageName}&laquo; wird installiert &hellip;</h1>
			<h2>Aktueller Schritt: <span id="packageInstallationAction">{lang}wcf.package.installation.step.prepare{/lang}</span></h2>
			<p><progress id="packageInstallationProgress" value="0" max="100" style="width: 200px;">0%</progress></p>
		</hgroup>
	</header>
	
	<div id="packageInstallationInnerContentContainer" class="" style="display: none;">
		<div id="packageInstallationInnerContent" class=""></div>
	</div>
</div>
