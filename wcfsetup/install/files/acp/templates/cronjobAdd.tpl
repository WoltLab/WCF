{include file='header'}

<header class="mainHeading">
	<img src="{@RELATIVE_WCF_DIR}icon/{$action}1.svg" alt="" />
	<hgroup>
		<h1>{lang}wcf.acp.cronjob.{$action}{/lang}</h1>
		<h2>{lang}wcf.acp.cronjob.subtitle{/lang}</h2>
	</hgroup>
</header>

<p class="info">{lang}wcf.acp.cronjob.intro{/lang}</p>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $success|isset}
	<p class="success">{lang}wcf.global.form.{$action}.success{/lang}</p>	
{/if}

<div class="contentHeader">
	<nav>
		<ul class="largeButtons">
			<li><a href="{link controller='CronjobList'}{/link}" title="{lang}wcf.acp.menu.link.cronjob.list{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/time1.svg" alt="" /> <span>{lang}wcf.acp.menu.link.cronjob.list{/lang}</span></a></li>
			{if $action == 'edit'}
				<li><a href="{link controller='CronjobExecute' id=$cronjobID}{/link}" title="{lang}wcf.acp.cronjob.execute{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/run1.svg" alt="" /> <span>{lang}wcf.acp.cronjob.execute{/lang}</span></a></li>
			{/if}
			
			{event name='largeButtons'}
		</ul>
	</nav>
</div>

<form method="post" action="{if $action == 'add'}{link controller='CronjobAdd'}{/link}{else}{link controller='CronjobEdit'}{/link}{/if}">
	<div class="border content">
		
		<fieldset>
			<legend>{lang}wcf.acp.cronjob.data{/lang}</legend>
			
			<dl{if $errorField == 'className'} class="formError"{/if}>
				<dt><label for="className">{lang}wcf.acp.cronjob.className{/lang}</label></dt>
				<dd>
					<input type="text" id="className" name="className" value="{$className}" class="long" required="required" pattern="^\\?([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*\\)*[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$" />
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
			
			<dl>
				<dt><label for="description">{lang}wcf.acp.cronjob.description{/lang}</label></dt>
				<dd>
					<input type="text" id="description" name="description" value="{$description}" class="long" />
				</dd>
			</dl>
			
			{event name='dataFields'}
		</fieldset>
		
		<fieldset>
			<legend>{lang}wcf.acp.cronjob.timing{/lang}</legend>
			
			<dl{if $errorField == 'startMinute'} class="formError"{/if}>
				<dt><label for="startMinute">{lang}wcf.acp.cronjob.startMinute{/lang}</label></dt>
				<dd>
					<input type="text" id="startMinute" name="startMinute" value="{$startMinute}" class="short" />
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
					<input type="text" id="startHour" name="startHour" value="{$startHour}" class="short" />
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
					<input type="text" id="startDom" name="startDom" value="{$startDom}" class="short" />
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
					<input type="text" id="startMonth" name="startMonth" value="{$startMonth}" class="short" />
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
					<input type="text" id="startDow" name="startDow" value="{$startDow}" class="short" />
					{if $errorField == 'startDow'}
						<small class="innerError">
							{lang}wcf.acp.cronjob.timing.error.{@$errorType}{/lang}
						</small>
					{/if}
					<small>{lang}wcf.acp.cronjob.startDow.description{/lang}</small>
				</dd>
			</dl>
			
			{event name='timingFields'}
		</fieldset>
		
		{event name='fieldsets'}
	</div>
	
	<div class="formSubmit">
		<input type="reset" value="{lang}wcf.global.button.reset{/lang}" accesskey="r" />
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		{@SID_INPUT_TAG}
 		{if $cronjobID|isset}<input type="hidden" name="id" value="{@$cronjobID}" />{/if}
	</div>
</form>

{include file='footer'}
