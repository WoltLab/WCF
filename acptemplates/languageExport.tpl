{include file='header'}

<header class="wcf-container wcf-mainHeading">
	<img src="{@RELATIVE_WCF_DIR}icon/download1.svg" alt="" class="wcf-containerIcon" />
	<hgroup class="wcf-containerContent">
		<h1>{lang}wcf.acp.language.export{/lang}</h1>
	</hgroup>
</header>

{if $errorField}
	<p class="wcf-error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $success|isset}
	<p class="wcf-success">{lang}wcf.acp.language.add.success{/lang}</p>	
{/if}

<div class="wcf-contentHeader">
	<nav>
		<ul class="wcf-largeButtons">
			<li><a href="{link controller='LanguageList'}{/link}" title="{lang}wcf.acp.menu.link.language.list{/lang}" class="wcf-button"><img src="{@RELATIVE_WCF_DIR}icon/language1.svg" alt="" /> <span>{lang}wcf.acp.menu.link.language.list{/lang}</span></a></li>
		</ul>
	</nav>
</div>

<form enctype="multipart/form-data" method="post" action="{link controller='LanguageExport' id=$languageID}{/link}">
	<div class="wcf-box wcf-marginTop wcf-boxPadding wcf-shadow1">
		<dl>
			<dt><label for="languageID">{lang}wcf.user.language{/lang}</label></dt>
			<dd>
				{htmlOptions options=$languages selected=$languageID name='languageID' id='languageID'}
			</dd>
		</dl>
		
		<dl>
			<dt><label for="selectedPackages">{lang}wcf.acp.language.export.selectPackages{/lang}</label></dt>
			<dd>
				<select id="selectedPackages" name="selectedPackages[]" multiple="multiple" size="20" class="long">
					<option value="*"{if $selectAllPackages} selected="selected"{/if}>{lang}wcf.acp.language.export.allPackages{/lang}</option>
					<option value="-">--------------------</option>
					{foreach from=$packages item=package}
						{assign var=loop value=$packageNameLength-$package->packageNameLength}
						<option value="{@$package->packageID}"{if $selectedPackages[$package->packageID]|isset} selected="selected"{/if}>{$package->packageName} {section name=i loop=$loop}&nbsp;{/section}&nbsp;&nbsp;{$package->package}</option>
					{/foreach}
				</select>
			</dd>
		</dl>
		
		<dl>
			<dd>
				<label for="exportCustomValues"><input type="checkbox" name="exportCustomValues" id="exportCustomValues" value="1" /> {lang}wcf.acp.language.export.customValues{/lang}</label>
			</dd>
		</dl>
	</div>
	
	<div class="wcf-formSubmit">
		<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
		<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
		{@SID_INPUT_TAG}
	</div>
</form>

{include file='footer'}