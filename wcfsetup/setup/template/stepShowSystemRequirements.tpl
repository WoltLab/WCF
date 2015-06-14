{include file='header'}

<header class="boxHeadline boxSubHeadline">
	<h2>{lang}wcf.global.systemRequirements.required{/lang}</h2>
</header>

<div class="container containerPadding marginTop">
	<fieldset>
		<legend>{lang}wcf.global.systemRequirements.php{/lang}</legend>
		<dl>
			<dt>{lang}wcf.global.systemRequirements.element.required{/lang} 5.5.4</dt>
			<dd>
				{lang}wcf.global.systemRequirements.element.yours{/lang} <span class="badge {if !$system.phpVersion.result}red{else}green{/if}">{$system.phpVersion.value}</span>
				{if !$system.phpVersion.result}<small>{lang}wcf.global.systemRequirements.php.description{/lang}</small>{/if}
			</dd>
		</dl>
	</fieldset>
	
	<fieldset>
		<legend>{lang}wcf.global.systemRequirements.memoryLimit{/lang}</legend>
		<dl>
			<dt>{lang}wcf.global.systemRequirements.element.required{/lang} 128 M</dt>
			<dd>
				{lang}wcf.global.systemRequirements.element.yours{/lang} <span class="badge {if !$system.memoryLimit.result}red{else}green{/if}">{$system.memoryLimit.value}</span>
				{if !$system.memoryLimit.result}<small>{lang}wcf.global.systemRequirements.memoryLimit.description{/lang}</small>{/if}
			</dd>
		</dl>
	</fieldset>
	
	<fieldset>
		<legend>{lang}wcf.global.systemRequirements.sql{/lang}</legend>
		<dl>
			<dt>{lang}wcf.global.systemRequirements.element.required{/lang} {lang}wcf.global.systemRequirements.active{/lang}</dt>
			<dd>
				{lang}wcf.global.systemRequirements.element.yours{/lang} <span class="badge {if !$system.sql.result}red{else}green{/if}">
				{if !$system.sql.result}{lang}wcf.global.systemRequirements.sql.notFound{/lang}{else}
					{implode from=$system.sql.value item=$sqlType glue=', '}{lang}wcf.global.configureDB.class.{@$sqlType}{/lang}{/implode}
				{/if}</span>
				{if !$system.sql.result}<small>{lang}wcf.global.systemRequirements.sql.description{/lang}</small>{/if}
			</dd>
		</dl>
	</fieldset>
</div>

<header class="boxHeadline boxSubHeadline">
	<h2>{lang}wcf.global.systemRequirements.recommended{/lang}</h2>
</header>

<div class="container containerPadding marginTop">
	<fieldset>
		<legend>{lang}wcf.global.systemRequirements.uploadMaxFilesize{/lang}</legend>
		<dl>
			<dt>{lang}wcf.global.systemRequirements.element.recommended{/lang} &gt; 0</dt>
			<dd>
				{lang}wcf.global.systemRequirements.element.yours{/lang} <span class="badge {if !$system.uploadMaxFilesize.result}yellow{else}green{/if}">{$system.uploadMaxFilesize.value}</span>
				{if !$system.uploadMaxFilesize.result}<small>{lang}wcf.global.systemRequirements.uploadMaxFilesize.description{/lang}</small>{/if}
			</dd>
		</dl>
	</fieldset>
	
	<fieldset>
		<legend>{lang}wcf.global.systemRequirements.gdLib{/lang}</legend>
		<dl>
			<dt>{lang}wcf.global.systemRequirements.element.recommended{/lang} 2.0.0</dt>
			<dd>
				{lang}wcf.global.systemRequirements.element.yours{/lang} <span class="badge {if !$system.gdLib.result}yellow{else}green{/if}">{$system.gdLib.value}</span>
				{if !$system.gdLib.result}<small>{lang}wcf.global.systemRequirements.gdLib.description{/lang}</small>{/if}
			</dd>
		</dl>
	</fieldset>
</div>

<form method="post" action="install.php">
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.next{/lang}"{if !$system.phpVersion.result || !$system.sql.result || !$system.memoryLimit.result} disabled="disabled"{/if} accesskey="s"/>
		<input type="hidden" name="step" value="{@$nextStep}" />
		<input type="hidden" name="tmpFilePrefix" value="{@$tmpFilePrefix}" />
		<input type="hidden" name="languageCode" value="{@$languageCode}" />
		<input type="hidden" name="dev" value="{@$developerMode}" />
	</div>
</form>

{include file='footer'}
