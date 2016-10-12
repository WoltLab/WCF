<dl>
	<dt><label>{lang}wcf.acp.user.exportEmailAddress.fileType{/lang}</label></dt>
	<dd>
		<label><input type="radio" name="fileType" value="csv"{if $fileType == 'csv'} checked{/if}> {lang}wcf.acp.user.exportEmailAddress.fileType.csv{/lang}</label>
		<label><input type="radio" name="fileType" value="xml"{if $fileType == 'xml'} checked{/if}> {lang}wcf.acp.user.exportEmailAddress.fileType.xml{/lang}</label>
	</dd>
</dl>

<dl id="separatorDiv"{if $fileType == 'xml'} style="display: none;"{/if}>
	<dt><label for="separator">{lang}wcf.acp.user.exportEmailAddress.separator{/lang}</label></dt>
	<dd>
		<input type="text" id="separator" name="separator" value="{$separator}" class="medium">
	</dd>
</dl>

<dl id="textSeparatorDiv"{if $fileType == 'xml'} style="display: none;"{/if}>
	<dt><label for="textSeparator">{lang}wcf.acp.user.exportEmailAddress.textSeparator{/lang}</label></dt>
	<dd>
		<input type="text" id="textSeparator" name="textSeparator" value="{$textSeparator}" class="medium">
	</dd>
</dl>

<script data-relocate="true">
	function toggleExportOptions(event) {
		var fileType = event.currentTarget.getAttribute('value');
		if (fileType === 'csv') {
			document.getElementById('separatorDiv').style.removeProperty('display');
			document.getElementById('textSeparatorDiv').style.removeProperty('display');
		}
		else {
			document.getElementById('separatorDiv').style.setProperty('display', 'none');
			document.getElementById('textSeparatorDiv').style.setProperty('display', 'none');
		}
	};
	
	var fileTypes = document.querySelectorAll('input[name=fileType]');
	for (var i = 0, length = fileTypes.length; i < length; i++) {
		fileTypes[i].addEventListener('change', toggleExportOptions);
	}
</script>
