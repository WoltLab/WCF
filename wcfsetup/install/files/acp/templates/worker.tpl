{if !$progress|isset}{assign var="progress" value=0}{/if}
{assign var="actionTitle" value=$pageTitle}
{capture assign="pageTitle"}{lang}wcf.acp.worker.progressBar{/lang} - {@$pageTitle}{/capture}
{include file='setupHeader'}

<script type="text/javascript">
	//<![CDATA[
	function showWindow(show) {
		if (show) {
			document.getElementById('iframe').style.visibility = 'visible';
			window.focus();
		} else {
			xHeight('iframe', 0);
			document.getElementById('iframe').style.visibility = 'hidden';
		}
	}
	
	function setCurrentStep(title) {
		document.getElementById('currentStep').innerHTML = title;
	}
	
	function setProgress(progress) {
		document.getElementById('progressBar').style.width = Math.round(300 * progress / 100) + 'px';
		{literal}
		document.getElementById('progressText').innerHTML = document.getElementById('progressText').innerHTML.replace(/\d{1,3}%/, progress + '%');
		document.title = document.title.replace(/\d{1,3}%/, progress + '%');
		{/literal}
	}
	
	onloadEvents.push(function() {
		document.getElementById('workerIcon').onclick = function(event) {
			if (!event) event = window.event;
			
			if (event.altKey) {
				showWindow(true);
				if (!xHeight('iframe')) {
					xHeight('iframe', 300);
				}
			}
		}
	});
	//]]>
</script>

<img id="workerIcon" class="icon" src="{@RELATIVE_WCF_DIR}icon/workerXL.png" alt="" />

<h1><b>{lang}wcf.global.pageTitle{/lang}</b><br />{@$actionTitle}</h1>

<div class="progress">
	<div id="progressBar" class="progressBar" style="width: {@300*$progress/100|round:0}px"></div>
	<div id="progressText" class="progressText">{lang}wcf.acp.worker.progressBar{/lang}</div>
</div>

<hr />

<h2>{lang}wcf.acp.worker.title{/lang}</h2>

<p>{lang}wcf.acp.worker.description{/lang}</p>

<fieldset>
	<legend>{lang}wcf.acp.worker.currentStep{/lang}</legend>
	
	<div class="inner">
		<div><span id="currentStep"></span></div>
		
		<iframe id="iframe" frameborder="0" src="{$url}"></iframe>
	</div>
</fieldset>

{include file='setupFooter'}