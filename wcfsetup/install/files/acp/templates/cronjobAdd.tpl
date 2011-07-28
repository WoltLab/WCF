{include file='header'}

<header class="mainHeading">
	<img src="{@RELATIVE_WCF_DIR}icon/cronjob{$action|ucfirst}L.png" alt="" />
	<hgroup>
		<h1>{lang}wcf.acp.cronjob.{$action}{/lang}</h1>
		<h2>{lang}wcf.acp.cronjob.subtitle{/lang}</h2>
	</hgroup>
</header>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $success|isset}
	<p class="success">{lang}wcf.acp.cronjob.{$action}.success{/lang}</p>	
{/if}

<p class="info">{lang}wcf.acp.cronjob.intro{/lang}</p>

<div class="contentHeader">
	<nav class="largeButtons">
		<ul>
			<li><a href="index.php?page=CronjobList{@SID_ARG_2ND}" title="{lang}wcf.acp.menu.link.cronjobs.view{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/cronjobsM.png" alt="" /> <span>{lang}wcf.acp.menu.link.cronjobs.view{/lang}</span></a></li>
			{if $action == 'edit'}<li><a href="index.php?action=CronjobExecute&amp;cronjobID={@$cronjobID}{@SID_ARG_2ND}" title="{lang}wcf.acp.cronjob.execute{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/cronjobExecuteM.png" alt="" /> <span>{lang}wcf.acp.cronjob.execute{/lang}</span></a></li>{/if}
		</ul>
	</nav>
</div>

<form method="post" action="index.php?form=Cronjob{$action|ucfirst}">
	<div class="border content">
		<div class="container-1">
			<fieldset>
				<legend>{lang}wcf.acp.cronjob.edit.data{/lang}</legend>
				
				<div id="classNameDiv" class="formElement{if $errorField == 'className'} formError{/if}">
					<div class="formFieldLabel">
						<label for="className">{lang}wcf.acp.cronjob.className{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" id="className" name="className" value="{$className}" class="inputText" />
						{if $errorField == 'className'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.acp.cronjob.error.empty{/lang}{/if}
								{if $errorType == 'doesNotExist'}{lang}wcf.acp.cronjob.error.doesNotExist{/lang}{/if}
							</p>
						{/if}
					</div>
					<div id="classNameHelpMessage" class="formFieldDesc hidden">
						<p>{lang}wcf.acp.cronjob.className.description{/lang}</p>
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('className');
				//]]></script>
				
				<div id="descriptionDiv" class="formElement">
					<div class="formFieldLabel">
						<label for="description">{lang}wcf.acp.cronjob.description{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" id="description" name="description" value="{$description}" class="inputText" />
					</div>
					<div id="descriptionHelpMessage" class="formFieldDesc hidden">
						<p>{lang}wcf.acp.cronjob.description.description{/lang}</p>
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('description');
				//]]></script>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.cronjob.edit.timing{/lang}</legend>
				<div id="startMinuteDiv" class="formElement{if $errorField == 'startMinute'} formError{/if}">
					<div class="formFieldLabel">
						<label for="startMinute">{lang}wcf.acp.cronjob.startMinute{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" id="startMinute" name="startMinute" value="{$startMinute}" class="inputText" />
						{if $errorField == 'startMinute'}
							<p class="innerError">
								{if $errorType == 'notValid'}{lang}wcf.acp.cronjob.error.notValid{/lang}{/if}
							</p>
						{/if}
					</div>
					<div id="startMinuteHelpMessage" class="formFieldDesc hidden">
						<p>{lang}wcf.acp.cronjob.startMinute.description{/lang}</p>
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('startMinute');
				//]]></script>
				
				<div id="startHourDiv" class="formElement{if $errorField == 'startHour'} formError{/if}">
					<div class="formFieldLabel">
						<label for="startHour">{lang}wcf.acp.cronjob.startHour{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" id="startHour" name="startHour" value="{$startHour}" class="inputText" />
						{if $errorField == 'startHour'}
							<p class="innerError">
								{if $errorType == 'notValid'}{lang}wcf.acp.cronjob.error.notValid{/lang}{/if}
							</p>
						{/if}
					</div>
					<div id="startHourHelpMessage" class="formFieldDesc hidden">
						<p>{lang}wcf.acp.cronjob.startHour.description{/lang}</p>
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('startHour');
				//]]></script>
				
				<div id="startDomDiv" class="formElement{if $errorField == 'startDom'} formError{/if}">
					<div class="formFieldLabel">
						<label for="startDom">{lang}wcf.acp.cronjob.startDom{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" id="startDom" name="startDom" value="{$startDom}" class="inputText" />
						{if $errorField == 'startDom'}
							<p class="innerError">
								{if $errorType == 'notValid'}{lang}wcf.acp.cronjob.error.notValid{/lang}{/if}
							</p>
						{/if}
					</div>
					<div id="startDomHelpMessage" class="formFieldDesc hidden">
						<p>{lang}wcf.acp.cronjob.startDom.description{/lang}</p>
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('startDom');
				//]]></script>
				
				<div id="startMonthDiv" class="formElement{if $errorField == 'startMonth'} formError{/if}">
					<div class="formFieldLabel">
						<label for="startMonth">{lang}wcf.acp.cronjob.startMonth{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" id="startMonth" name="startMonth" value="{$startMonth}" class="inputText" />
						{if $errorField == 'startMonth'}
							<p class="innerError">
								{if $errorType == 'notValid'}{lang}wcf.acp.cronjob.error.notValid{/lang}{/if}
							</p>
						{/if}
					</div>
					<div id="startMonthHelpMessage" class="formFieldDesc hidden">
						<p>{lang}wcf.acp.cronjob.startMonth.description{/lang}</p>
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('startMonth');
				//]]></script>
				
				<div id="startDowDiv" class="formElement{if $errorField == 'startDow'} formError{/if}">
					<div class="formFieldLabel">
						<label for="startDow">{lang}wcf.acp.cronjob.startDow{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" id="startDow" name="startDow" value="{$startDow}" class="inputText" />
						{if $errorField == 'startDow'}
							<p class="innerError">
								{if $errorType == 'notValid'}{lang}wcf.acp.cronjob.error.notValid{/lang}{/if}
							</p>
						{/if}
					</div>
					<div id="startDowHelpMessage" class="formFieldDesc hidden">
						<p>{lang}wcf.acp.cronjob.startDow.description{/lang}</p>
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('startDow');
				//]]></script>
			</fieldset>
			
			{if $additionalFields|isset}{@$additionalFields}{/if}
		</div>
	</div>
	
	<div class="formSubmit">
		<input type="reset" value="{lang}wcf.global.button.reset{/lang}" accesskey="r" />
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		{@SID_INPUT_TAG}
 		{if $cronjobID|isset}<input type="hidden" name="cronjobID" value="{@$cronjobID}" />{/if}
	</div>
</form>

{include file='footer'}
