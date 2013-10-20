{include file='header'}

<script data-relocate="true" src="//ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js"></script>
<script data-relocate="true">
	//<![CDATA[
	if (window.jQuery) {
		$(function() {
			$('#wcfUrlContainer').show();
			
			// data
			var $domainName = '{@$domainName|encodeJS}';
			var $installScriptDir = '{@$installScriptDir|encodeJS}';
			var $installScriptUrl = '{@$installScriptUrl|encodeJS}';
			var $invalidErrorMessage = '{lang}wcf.global.wcfDir.error.invalid{/lang}';
			var $wcfDir = $('#wcfDir');
			var $wcfUrl = $('#wcfUrl');
			
			function updateWcfUrl() {
				// split paths and remove empty parts
				var $installScriptDirs = removeEmptyDirParts($installScriptDir.split('/'));
				var $wcfDirs = removeEmptyDirParts($wcfDir.val().split('/'));
				var $installScriptUrlDirs = removeEmptyDirParts($installScriptUrl.split('/'));
				
				// get relative path
				var $relativePathDirs = [];
				var $max = ($wcfDirs.length > $installScriptDirs.length) ? $wcfDirs.length : $installScriptDirs.length;
				for (var $i = 0; $i < $max; $i++) {
					if ($i < $installScriptDirs.length && $i < $wcfDirs.length) {
						if ($installScriptDirs[$i] != $wcfDirs[$i]) {
							$wcfDirs.splice(0, i);
							for (var $j = 0, $length = $installScriptDirs.length - $i; $j < $length; $j++) $relativePathDirs.push('..');
							$relativePathDirs = $relativePathDirs.concat($wcfDirs);
							break;
						}
					}	
					// go up one level
					else if ($i < $installScriptDirs.length && $i >= $wcfDirs.length) {
						$relativePathDirs.push('..');
					}
					else {
						$relativePathDirs.push($wcfDirs[$i]);
					}
				}
				
				// loop dirs
				for (var $i = 0; $i < $relativePathDirs.length; $i++) {
					if ($relativePathDirs[$i] == '..') {
						if ($installScriptUrlDirs.length < 1) {
							$wcfUrl.html($invalidErrorMessage);
							return;
						}
						
						$installScriptUrlDirs.pop();
					}
					else {
						$installScriptUrlDirs.push($relativePathDirs[$i]);
					}
				}
				
				// implode and show result
				var $result = $domainName;
				for (var $i = 0; $i < $installScriptUrlDirs.length; $i++) $result += '/' + $installScriptUrlDirs[$i];
				$wcfUrl.html($result);
			}
			
			function removeEmptyDirParts(dir) {
				for (var $i = dir.length; $i >= 0; $i--) {
					if (dir[$i] == '' || dir[$i] == '.') {
						dir.splice($i, 1);
					}
				}
				
				return dir;
			}
			
			$wcfDir.keyup(updateWcfUrl).blur(updateWcfUrl);
			updateWcfUrl();
		});
	}
	//]]>
</script>

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

{include file='footer'}
