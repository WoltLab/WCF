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
	<nav class="largeButtons">
		<ul>
			<li><a href="index.php?page=CronjobList{@SID_ARG_2ND}" title="{lang}wcf.acp.menu.link.cronjob.list{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/time1.svg" alt="" /> <span>{lang}wcf.acp.menu.link.cronjob.list{/lang}</span></a></li>
			{if $action == 'edit'}<li><a href="index.php?action=CronjobExecute&amp;cronjobID={@$cronjobID}{@SID_ARG_2ND}" title="{lang}wcf.acp.cronjob.execute{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/run1.svg" alt="" /> <span>{lang}wcf.acp.cronjob.execute{/lang}</span></a></li>{/if}
		</ul>
	</nav>
</div>

<form method="post" action="index.php?form=Cronjob{$action|ucfirst}">
	<div class="border content">
		
		<fieldset>
			<legend>{lang}wcf.acp.cronjob.data{/lang}</legend>
			
			<dl id="classNameDiv"{if $errorField == 'className'} class="formError"{/if}>
				<dt><label for="className">{lang}wcf.acp.cronjob.className{/lang}</label></dt>
				<dd>
					<input type="text" id="className" name="className" value="{$className}" class="long" />
					{if $errorField == 'className'}
						<small class="innerError">
							<span class="arrowOuter" style="display: none;"><span class="arrowInner"></span></span>
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.acp.cronjob.className.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
					<small id="classNameHelpMessage">{lang}wcf.acp.cronjob.className.description{/lang}</small>
				</dd>
			</dl>
			
			<dl id="descriptionDiv">
				<dt><label for="description">{lang}wcf.acp.cronjob.description{/lang}</label></dt>
				<dd>
					<input type="text" id="description" name="description" value="{$description}" class="long" />
					<small id="descriptionHelpMessage">{lang}wcf.acp.cronjob.description.description{/lang}</small>
				</dd>
			</dl>
		</fieldset>
		
		<fieldset>
			<legend>{lang}wcf.acp.cronjob.timing{/lang}</legend>
			
			<dl id="startMinuteDiv"{if $errorField == 'startMinute'} class="formError"{/if}>
				<dt><label for="startMinute">{lang}wcf.acp.cronjob.startMinute{/lang}</label></dt>
				<dd>
					<input type="text" id="startMinute" name="startMinute" value="{$startMinute}" class="short" />
					{if $errorField == 'startMinute'}
						<small class="innerError">
							<span class="arrowOuter" style="display: none;"><span class="arrowInner"></span></span>
							{lang}wcf.acp.cronjob.timing.error.{@$errorType}{/lang}
						</small>
					{/if}
					<small id="startMinuteHelpMessage">{lang}wcf.acp.cronjob.startMinute.description{/lang}</small>
				</dd>
			</dl>
			
			<dl id="startHourDiv"{if $errorField == 'startHour'} class="formError"{/if}>
				<dt><label for="startHour">{lang}wcf.acp.cronjob.startHour{/lang}</label></dt>
				<dd>
					<input type="text" id="startHour" name="startHour" value="{$startHour}" class="short" />
					{if $errorField == 'startHour'}
						<small class="innerError">
							<span class="arrowOuter" style="display: none;"><span class="arrowInner"></span></span>
							{lang}wcf.acp.cronjob.timing.error.{@$errorType}{/lang}
						</small>
					{/if}
					<small id="startHourHelpMessage">{lang}wcf.acp.cronjob.startHour.description{/lang}</small>
				</dd>
			</dl>
			
			<dl id="startDomDiv"{if $errorField == 'startDom'} class="formError"{/if}>
				<dt><label for="startDom">{lang}wcf.acp.cronjob.startDom{/lang}</label></dt>
				<dd>
					<input type="text" id="startDom" name="startDom" value="{$startDom}" class="short" />
					{if $errorField == 'startDom'}
						<small class="innerError">
							<span class="arrowOuter" style="display: none;"><span class="arrowInner"></span></span>
							{lang}wcf.acp.cronjob.timing.error.{@$errorType}{/lang}
						</small>
					{/if}
					<small id="startDomHelpMessage">{lang}wcf.acp.cronjob.startDom.description{/lang}</small>
				</dd>
			</dl>
			
			<dl id="startMonthDiv"{if $errorField == 'startMonth'} class="formError"{/if}>
				<dt><label for="startMonth">{lang}wcf.acp.cronjob.startMonth{/lang}</label></dt>
				<dd>
					<input type="text" id="startMonth" name="startMonth" value="{$startMonth}" class="short" />
					{if $errorField == 'startMonth'}
						<small class="innerError">
							<span class="arrowOuter" style="display: none;"><span class="arrowInner"></span></span>
							{lang}wcf.acp.cronjob.timing.error.{@$errorType}{/lang}
						</small>
					{/if}
					<small id="startMonthHelpMessage">{lang}wcf.acp.cronjob.startMonth.description{/lang}</small>
				</dd>
			</dl>
			
			<dl id="startDowDiv"{if $errorField == 'startDow'} class="formError"{/if}>
				<dt><label for="startDow">{lang}wcf.acp.cronjob.startDow{/lang}</label></dt>
				<dd>
					<input type="text" id="startDow" name="startDow" value="{$startDow}" class="short" />
					{if $errorField == 'startDow'}
						<small class="innerError">
							<span class="arrowOuter" style="display: none;"><span class="arrowInner"></span></span>
							{lang}wcf.acp.cronjob.timing.error.{@$errorType}{/lang}
						</small>
					{/if}
					<small id="startDowHelpMessage">{lang}wcf.acp.cronjob.startDow.description{/lang}</small>
				</dd>
			</dl>
		</fieldset>
		
		{if $additionalFields|isset}{@$additionalFields}{/if}
	</div>
	
	<div class="formSubmit">
		<input type="reset" value="{lang}wcf.global.button.reset{/lang}" accesskey="r" />
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		{@SID_INPUT_TAG}
 		{if $cronjobID|isset}<input type="hidden" name="cronjobID" value="{@$cronjobID}" />{/if}
	</div>
</form>

{include file='footer'}
