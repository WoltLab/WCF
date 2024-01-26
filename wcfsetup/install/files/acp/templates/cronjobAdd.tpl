{include file='header' pageTitle='wcf.acp.cronjob.'|concat:$action}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.cronjob.{$action}{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='CronjobList'}{/link}" class="button">{icon name='list'} <span>{lang}wcf.acp.menu.link.cronjob.list{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

<woltlab-core-notice type="info">{lang}wcf.acp.cronjob.intro{/lang}</woltlab-core-notice>

{include file='shared_formNotice'}

<form method="post" action="{if $action == 'add'}{link controller='CronjobAdd'}{/link}{else}{link controller='CronjobEdit' id=$cronjobID}{/link}{/if}">
	<div class="section">
		<dl{if $errorField == 'className'} class="formError"{/if}>
			<dt><label for="className">{lang}wcf.acp.cronjob.className{/lang}</label></dt>
			<dd>
				<input type="text" id="className" name="className" value="{$className}" required autofocus pattern="^\\?([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*\\)*[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$" class="long">
				{if $errorField == 'className'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}wcf.acp.cronjob.className.error.{@$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
		<dl{if $errorField == 'description'} class="formError"{/if}>
			<dt><label for="description">{lang}wcf.acp.cronjob.description{/lang}</label></dt>
			<dd>
				<input type="text" id="description" name="description" value="{$i18nPlainValues['description']}" class="long">
				{if $errorField == 'description'}
					<small class="innerError">
						{if $errorType == 'empty' || $errorType == 'multilingual'}
							{lang}wcf.global.form.error.{@$errorType}{/lang}
						{else}
							{lang}wcf.acp.cronjob.className.error.{@$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		{include file='shared_multipleLanguageInputJavascript' elementIdentifier='description' forceSelection=false}
		
		{event name='dataFields'}
	</div>
	
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.acp.cronjob.timing{/lang}</h2>
		
		<dl{if $errorField == 'startMinute'} class="formError"{/if}>
			<dt><label for="startMinute">{lang}wcf.acp.cronjob.startMinute{/lang}</label></dt>
			<dd>
				<input type="text" id="startMinute" name="startMinute" value="{$startMinute}" class="short">
				{if $errorField == 'startMinute'}
					<small class="innerError">
						{lang}wcf.acp.cronjob.timing.error.{@$errorType}{/lang}
					</small>
				{/if}
				<small>{lang}wcf.acp.cronjob.startMinute.description{/lang}</small>
			</dd>
		</dl>
		
		<dl{if $errorField == 'startHour'} class="formError"{/if}>
			<dt><label for="startHour">{lang}wcf.acp.cronjob.startHour{/lang}</label></dt>
			<dd>
				<input type="text" id="startHour" name="startHour" value="{$startHour}" class="short">
				{if $errorField == 'startHour'}
					<small class="innerError">
						{lang}wcf.acp.cronjob.timing.error.{@$errorType}{/lang}
					</small>
				{/if}
				<small>{lang}wcf.acp.cronjob.startHour.description{/lang}</small>
			</dd>
		</dl>
		
		<dl{if $errorField == 'startDom'} class="formError"{/if}>
			<dt><label for="startDom">{lang}wcf.acp.cronjob.startDom{/lang}</label></dt>
			<dd>
				<input type="text" id="startDom" name="startDom" value="{$startDom}" class="short">
				{if $errorField == 'startDom'}
					<small class="innerError">
						{lang}wcf.acp.cronjob.timing.error.{@$errorType}{/lang}
					</small>
				{/if}
				<small>{lang}wcf.acp.cronjob.startDom.description{/lang}</small>
			</dd>
		</dl>
		
		<dl{if $errorField == 'startMonth'} class="formError"{/if}>
			<dt><label for="startMonth">{lang}wcf.acp.cronjob.startMonth{/lang}</label></dt>
			<dd>
				<input type="text" id="startMonth" name="startMonth" value="{$startMonth}" class="short">
				{if $errorField == 'startMonth'}
					<small class="innerError">
						{lang}wcf.acp.cronjob.timing.error.{@$errorType}{/lang}
					</small>
				{/if}
				<small>{lang}wcf.acp.cronjob.startMonth.description{/lang}</small>
			</dd>
		</dl>
		
		<dl{if $errorField == 'startDow'} class="formError"{/if}>
			<dt><label for="startDow">{lang}wcf.acp.cronjob.startDow{/lang}</label></dt>
			<dd>
				<input type="text" id="startDow" name="startDow" value="{$startDow}" class="short">
				{if $errorField == 'startDow'}
					<small class="innerError">
						{lang}wcf.acp.cronjob.timing.error.{@$errorType}{/lang}
					</small>
				{/if}
				<small>{lang}wcf.acp.cronjob.startDow.description{/lang}</small>
			</dd>
		</dl>
		
		{event name='timingFields'}
	</section>
	
	{event name='sections'}
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		{csrfToken}
	</div>
</form>

{include file='footer'}
