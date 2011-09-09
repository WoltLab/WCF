{include file='header'}

<hgroup class="subHeading">
	<h1>{lang}wcf.global.wcfDir{/lang}</h1>
	<h2>{lang}wcf.global.wcfDir.description{/lang}</h2>
</hgroup>

{if $foundDirectory}
	<p>{lang}wcf.global.wcfDir.foundDirectory{/lang}</p>
{/if}

{if $exception|isset}
	<p class="error">{lang}wcf.global.wcfDir.error{/lang}</p>
{/if}

<form method="post" action="install.php">
	<fieldset>
		<legend>{lang}wcf.global.wcfDir.dir{/lang}</legend>
		
		<dl>
			<dt><label for="wcfDir">{lang}wcf.global.wcfDir.dir.description{/lang}</label></dt>
			<dd><input type="text" id="wcfDir" name="wcfDir" value="{$wcfDir}" class="long" /></dd>
		</dl>
		<dl>
			<dt><label for="wcfUrl">{lang}wcf.global.wcfDir.url.description{/lang}</label></dt>
			<dd><input type="text" id="wcfUrl" name="wcfUrl" value="" readonly="readonly" class="long" /></dd>
		</dl>
		
	</fieldset>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.next{/lang}" accesskey="s" />
		<input type="hidden" name="step" value="{@$nextStep}" />
		<input type="hidden" name="tmpFilePrefix" value="{@$tmpFilePrefix}" />
		<input type="hidden" name="languageCode" value="{@$languageCode}" />
		<input type="hidden" name="dev" value="{@$developerMode}" />
	</div>
</form>

<script type="text/javascript">
	//<![CDATA[
	// data
	var domainName = '{@$domainName|encodeJS}';
	var installScriptDir = '{@$installScriptDir|encodeJS}';
	var installScriptUrl = '{@$installScriptUrl|encodeJS}';
	var invalidErrorMessage = '{lang}wcf.global.wcfDir.error.invalid{/lang}';
	
	// function
	function refreshWcfUrl() {
		// split paths
		var installScriptDirs = installScriptDir.split('/');
		var wcfDirs = document.getElementById('wcfDir').value.split('/');
		var installScriptUrlDirs = installScriptUrl.split('/');
		
		// remove empty elements
		for (var i = installScriptDirs.length; i >= 0; i--) if (installScriptDirs[i] == '' || installScriptDirs[i] == '.') installScriptDirs.splice(i, 1);
		for (var i = wcfDirs.length; i >= 0; i--) if (wcfDirs[i] == '' || wcfDirs[i] == '.') wcfDirs.splice(i, 1);
		for (var i = installScriptUrlDirs.length; i >= 0; i--) if (installScriptUrlDirs[i] == '') installScriptUrlDirs.splice(i, 1);
		
		// get relative path
		var relativePathDirs = new Array();
		var max = (wcfDirs.length > installScriptDirs.length ? wcfDirs.length : installScriptDirs.length);
		for (var i = 0; i < max; i++) {
			if (i < installScriptDirs.length && i < wcfDirs.length) {
				if (installScriptDirs[i] != wcfDirs[i]) {
					wcfDirs.splice(0, i);
					for (var j = 0; j < installScriptDirs.length - i; j++) relativePathDirs.push('..');
					relativePathDirs = relativePathDirs.concat(wcfDirs);
					break;
				}
			}	
			// go up one level
			else if (i < installScriptDirs.length && i >= wcfDirs.length) {
				relativePathDirs.push('..');
			}
			else {
				relativePathDirs.push(wcfDirs[i]);
			}
		}
		
		// loop dirs
		for (var i = 0; i < relativePathDirs.length; i++) {
			if (relativePathDirs[i] == '..') {
				if (installScriptUrlDirs.length < 1) {
					document.getElementById('wcfUrl').value = invalidErrorMessage;
					return;
				}
				
				installScriptUrlDirs.pop();
			}
			else {
				installScriptUrlDirs.push(relativePathDirs[i]);
			}
		}
		
		// implode and show result
		var result = domainName;
		for (var i = 0; i < installScriptUrlDirs.length; i++) result += '/' + installScriptUrlDirs[i];
		document.getElementById('wcfUrl').value = result;
	}
	
	window.onload = function() {
		// set onchange listener
		document.getElementById('wcfDir').onkeyup = function() { refreshWcfUrl(); };
		
		// set onblur listener
		document.getElementById('wcfDir').onblur = function() { refreshWcfUrl(); };
		
		// set default value
		refreshWcfUrl();
	}
	//]]>
</script>

{include file='footer'}
