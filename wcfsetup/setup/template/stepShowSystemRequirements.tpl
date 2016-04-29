{include file='header'}

<section class="section">
	<h2 class="sectionTitle">{lang}wcf.global.systemRequirements.required{/lang}</h2>
	
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.global.systemRequirements.php{/lang}</h2>
		
		<div class="row rowColGap formGrid">
			<dl class="col-xs-12 col-md-6">
				<dt>{lang}wcf.global.systemRequirements.element.required{/lang}</dt>
				<dd>5.5.4</dd>
			</dl>
			
			<dl class="col-xs-12 col-md-6">
				<dt>{lang}wcf.global.systemRequirements.element.yours{/lang}</dt>
				<dd>
					<span class="badge {if !$system.phpVersion.result}red{else}green{/if}">{$system.phpVersion.value}</span>
					{if !$system.phpVersion.result}<small>{lang}wcf.global.systemRequirements.php.description{/lang}</small>{/if}
				</dd>
			</dl>
		</div>
	</section>
		
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.global.systemRequirements.memoryLimit{/lang}</h2>
		
		<div class="row rowColGap formGrid">
			<dl class="col-xs-12 col-md-6">
				<dt>{lang}wcf.global.systemRequirements.element.required{/lang}</dt>
				<dd>128M</dd>
			</dl>
			
			<dl class="col-xs-12 col-md-6">
				<dt>{lang}wcf.global.systemRequirements.element.yours{/lang}</dt>
				<dd>
					<span class="badge {if !$system.memoryLimit.result}red{else}green{/if}">{$system.memoryLimit.value}</span>
					{if !$system.memoryLimit.result}<small>{lang}wcf.global.systemRequirements.memoryLimit.description{/lang}</small>{/if}
				</dd>
			</dl>
		</div>
	</section>
	
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.global.systemRequirements.sql{/lang}</h2>
		
		<div class="row rowColGap formGrid">
			<dl class="col-xs-12 col-md-6">
				<dt>{lang}wcf.global.systemRequirements.element.required{/lang}</dt>
				<dd>{lang}wcf.global.systemRequirements.active{/lang}</dd>
			</dl>
			
			<dl class="col-xs-12 col-md-6">
				<dt>{lang}wcf.global.systemRequirements.element.yours{/lang}</dt>
				<dd>
					<span class="badge {if !$system.sql.result}red{else}green{/if}">
					{if !$system.sql.result}{lang}wcf.global.systemRequirements.sql.notFound{/lang}{else}
						{implode from=$system.sql.value item=$sqlType glue=', '}{lang}wcf.global.configureDB.class.{@$sqlType}{/lang}{/implode}
					{/if}</span>
					{if !$system.sql.result}<small>{lang}wcf.global.systemRequirements.sql.description{/lang}</small>{/if}
				</dd>
			</dl>
		</div>
	</section>
</section>

<section class="section">
	<h2 class="sectionTitle">{lang}wcf.global.systemRequirements.recommended{/lang}</h2>
	
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.global.systemRequirements.uploadMaxFilesize{/lang}</h2>
		
		<div class="row rowColGap formGrid">
			<dl class="col-xs-12 col-md-6">
				<dt>{lang}wcf.global.systemRequirements.element.recommended{/lang}</dt>
				<dd>&gt; 0</dd>
			</dl>
			
			<dl class="col-xs-12 col-md-6">
				<dt>{lang}wcf.global.systemRequirements.element.yours{/lang}</dt>
				<dd>
					<span class="badge {if !$system.uploadMaxFilesize.result}yellow{else}green{/if}">{$system.uploadMaxFilesize.value}</span>
					{if !$system.uploadMaxFilesize.result}<small>{lang}wcf.global.systemRequirements.uploadMaxFilesize.description{/lang}</small>{/if}
				</dd>
			</dl>
		</div>
	</section>
	
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.global.systemRequirements.gdLib{/lang}</h2>
		
		<div class="row rowColGap formGrid">
			<dl class="col-xs-12 col-md-6">
				<dt>{lang}wcf.global.systemRequirements.element.recommended{/lang}</dt>
				<dd>2.0.0</dd>
			</dl>
			
			<dl class="col-xs-12 col-md-6">
				<dt>{lang}wcf.global.systemRequirements.element.yours{/lang}</dt>
				<dd>
					<span class="badge {if !$system.gdLib.result}yellow{else}green{/if}">{$system.gdLib.value}</span>
					{if !$system.gdLib.result}<small>{lang}wcf.global.systemRequirements.gdLib.description{/lang}</small>{/if}
				</dd>
			</dl>
		</div>
	</section>
</section>

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
