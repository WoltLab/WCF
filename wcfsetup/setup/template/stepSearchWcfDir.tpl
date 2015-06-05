{include file='header'}

<header class="boxHeadline boxSubHeadline">
	<h2>{lang}wcf.global.wcfDir{/lang}</h2>
	<p>{lang}wcf.global.wcfDir.description{/lang}</p>
</header>

{if $invalidDirectory}
	<p class="error">{lang}wcf.global.wcfDir.error.invalidDirectory{/lang}</p>
{/if}

{if $exception|isset}
	<p class="error">{lang}wcf.global.wcfDir.error{/lang}</p>
{/if}

<form method="post" action="install.php">
	<div class="container containerPadding marginTop">
		<fieldset>
			<legend>{lang}wcf.global.wcfDir.dir{/lang}</legend>
			<small>{lang}wcf.global.wcfDir.dir.info{/lang}</small>
			
			<dl>
				<dt><label for="wcfDir">{lang}wcf.global.wcfDir.dir{/lang}</label></dt>
				<dd>
					<input type="text" id="wcfDir" name="wcfDir" value="{$wcfDir}" class="long" />
					<small>{lang}wcf.global.wcfDir.dir.description{/lang}</small>
				</dd>
			</dl>
			<dl id="wcfUrlContainer" style="display: none;">
				<dt><label for="wcfUrl">{lang}wcf.global.wcfDir.url{/lang}</label></dt>
				<dd>
					<p id="wcfUrl"></p>
					<small>{lang}wcf.global.wcfDir.url.description{/lang}</small>
				</dd>
			</dl>
		</fieldset>
	</div>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.next{/lang}" accesskey="s" />
		<input type="hidden" name="step" value="{@$nextStep}" />
		<input type="hidden" name="tmpFilePrefix" value="{@$tmpFilePrefix}" />
		<input type="hidden" name="languageCode" value="{@$languageCode}" />
		<input type="hidden" name="dev" value="{@$developerMode}" />
	</div>
</form>

<script data-relocate="true">
	document.getElementById('wcfUrlContainer').style.removeProperty('display');
	
	// data
	var DOMAIN_NAME = '{@$domainName|encodeJS}';
	var INSTALL_SCRIPT_DIR = '{@$installScriptDir|encodeJS}';
	var INSTALL_SCRIPT_URL = '{@$installScriptUrl|encodeJS}';
	var INVALID_ERROR_MESSAGE = '{lang}wcf.global.wcfDir.error.invalid{/lang}';
	var wcfDir = document.getElementById('wcfDir');
	var wcfUrl = document.getElementById('wcfUrl');
	
	function removeEmptyDirParts(dir) {
		for (var i = dir.length; i >= 0; i--) {
			if (dir[i] == '' || dir[i] == '.') {
				dir.splice(i, 1);
			}
		}
		
		return dir;
	}
	
	function updateWcfUrl() {
		// split paths and remove empty parts
		var installScriptDirs = removeEmptyDirParts(INSTALL_SCRIPT_DIR.split('/'));
		var wcfDirs = removeEmptyDirParts(wcfDir.value.split('/'));
		var installScriptUrlDirs = removeEmptyDirParts(INSTALL_SCRIPT_URL.split('/'));
		
		// get relative path
		var relativePathDirs = [];
		var max = (wcfDirs.length > installScriptDirs.length) ? wcfDirs.length : installScriptDirs.length;
		for (var i = 0; i < max; i++) {
			if (i < installScriptDirs.length && i < wcfDirs.length) {
				if (installScriptDirs[i] !== wcfDirs[i]) {
					wcfDirs.splice(0, i);
					
					for (var j = 0, length = installScriptDirs.length - i; j < length; j++) {
						$relativePathDirs.push('..');
					}
					
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
					wcfUrl.textContent = INVALID_ERROR_MESSAGE;
					return;
				}
				
				installScriptUrlDirs.pop();
			}
			else {
				installScriptUrlDirs.push(relativePathDirs[i]);
			}
		}
		
		wcfUrl.textContent = DOMAIN_NAME + (installScriptUrlDirs.length ? '/' : '') + installScriptUrlDirs.join('/');
	}
	
	wcfDir.addEventListener('keyup', updateWcfUrl);
	wcfDir.addEventListener('blur', updateWcfUrl);
	
	updateWcfUrl();
</script>

{include file='footer'}
