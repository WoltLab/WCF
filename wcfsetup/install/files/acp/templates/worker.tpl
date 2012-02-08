<div id="workerContainer">
	<header class="mainHeading">
		<img src="{@RELATIVE_WCF_DIR}icon/working2.svg" alt="" />
		<hgroup>
			<h1>Aufgaben werden ausgeführt &hellip;</h1>
			<h2>Aktueller Schritt: <span id="workerAction">{lang}wcf.package.installation.step.prepare{/lang}</span></h2>
			<p><progress id="workerProgress" value="0" max="100">0%</progress></p>
		</hgroup>
	</header>
	
	<div id="workerInnerContentContainer" style="display: none;">
		<div id="workerInnerContent"></div>
	</div>
</div>
