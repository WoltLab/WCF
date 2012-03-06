{include file='header'}

<script type="text/javascript">
	//<![CDATA[
	$(function() {
		// count checkboxes and those already checked (faster than retrieving that number with every change)
		var $checked = $('input[name="packageUpdateServerIDs[]"]:checked').length;
		var $count = $('input[name="packageUpdateServerIDs[]"]').length;
		
		// handle clicks on 'seach all'
		$('input[name="checkUncheck"]').change(function() {
			if ($(this).attr('checked')) {
				$('input[name="packageUpdateServerIDs[]"]').attr('checked', 'checked');
				$checked = $count;
			}
			else {
				$('input[name="packageUpdateServerIDs[]"]').attr('checked', '');
				$checked = 0;
			}
		});
		
		// handle clicks on each other checkbox (literally each server)
		$('input[name="packageUpdateServerIDs[]"]').change(function() {
			if ($(this).attr('checked')) {
				$checked++;
				
				if ($checked === $count) {
					$('input[name="checkUncheck"]').attr('checked', 'checked');
				}
			}
			else {
				$('input[name="checkUncheck"]').attr('checked', '');
				$checked--;
			}
		});
	});
	//]]>
</script>

<header class="wcf-container wcf-mainHeading">
	<img src="{@$__wcf->getPath()}icon/search1.svg" alt="" class="wcf-containerIcon" />
	<hgroup class="wcf-containerContent">
		<h1>{lang}wcf.acp.packageUpdate.search{/lang}</h1>
	</hgroup>
</header>

{if $errorField != ''}
	<p class="wcf-error">{lang}wcf.acp.packageUpdate.noneAvailable{/lang}</p>
{/if}

{if !$updateServers|count}
	<p class="wcf-warning">{lang}wcf.acp.updateServer.view.noneAvailable{/lang}</p>
{else}
	<form method="post" action="{link controller='PackageUpdateSearch'}{/link}">
		<div class="wcf-box wcf-boxPadding wcf-content">
			
			<fieldset>
				<legend>{lang}wcf.acp.packageUpdate.search.server{/lang}</legend>
				
				<dl>
					<dt></dt>
					<dd>
						<label><input type="checkbox" name="checkUncheck" value="" /> {lang}wcf.acp.packageUpdate.search.server.all{/lang}</label> 
					</dd>
				</dl>
				
				<dl id="updateServerList">
					<dt></dt>
					{foreach from=$updateServers item=updateServer}
						<dd>
							<label><input type="checkbox" name="packageUpdateServerIDs[]" value="{@$updateServer->packageUpdateServerID}" {if $updateServer->packageUpdateServerID|in_array:$packageUpdateServerIDs}checked="checked" {/if}/> {$updateServer->serverURL}</label>
						</dd>
					{/foreach}
				</dl>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.packageUpdate.search.conditions{/lang}</legend>
				
				<dl>
					<dt><label for="packageName">{lang}wcf.acp.packageUpdate.search.packageName{/lang}</label></dt>
					<dd>
						<input type="text" id="packageName" name="packageName" value="{$packageName}" class="long" />
					</dd>
					<dd>
						<label><input type="checkbox" name="searchDescription" value="1" {if $searchDescription == 1}checked="checked" {/if}/> {lang}wcf.acp.packageUpdate.search.searchDescription{/lang}</label>
					</dd>
				</dl>
				
				<dl>
					<dt><label for="author">{lang}wcf.acp.packageUpdate.search.author{/lang}</label></dt>
					<dd>
						<input type="text" id="author" name="author" value="{$author}" class="medium" />
					</dd>
				</dl>
				
				<dl>
					<dt>{lang}wcf.acp.packageUpdate.search.type{/lang}</dt>
					<dd>
						<label><input type="checkbox" name="isApplication" value="1" {if $isApplication == 1}checked="checked" {/if}/> {lang}wcf.acp.packageUpdate.search.type.isApplication{/lang}</label> 
					</dd>
					<dd>
						<label><input type="checkbox" name="plugin" value="1" {if $plugin == 1}checked="checked" {/if}/> {lang}wcf.acp.packageUpdate.search.type.plugin{/lang}</label> 
					</dd>
					<dd>
						<label><input type="checkbox" name="other" value="1" {if $other == 1}checked="checked" {/if}/> {lang}wcf.acp.packageUpdate.search.type.other{/lang}</label> 
					</dd>
				</dl>
				
				<dl>
					<dt></dt>
					<dd>
						<label><input type="checkbox" name="ignoreUniques" value="1" {if $ignoreUniques == 1}checked="checked" {/if}/> {lang}wcf.acp.packageUpdate.search.ignoreUniques{/lang}</label>
					</dd>
				</dl>
			</fieldset>
			
		</div>
		
		<div class="wcf-formSubmit">
			<input type="reset" value="{lang}wcf.global.button.reset{/lang}" accesskey="r" />
			<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
			{@SID_INPUT_TAG}
	 	</div>
	</form>
{/if}

{include file='footer'}
