{include file='header'}

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/cronjobs{$action|ucfirst}L.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.cronjob.{$action}{/lang}</h2>
		<p>{lang}wcf.acp.cronjob.subtitle{/lang}</p>
	</div>
</div>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $success|isset}
	<p class="success">{lang}wcf.acp.cronjob.{$action}.success{/lang}</p>	
{/if}

<p class="info">{lang}wcf.acp.cronjob.intro{/lang}</p>

<div class="contentHeader">
	<div class="largeButtons">
		<ul>
			<li><a href="index.php?page=CronjobList{@SID_ARG_2ND}" title="{lang}wcf.acp.menu.link.cronjobs.view{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/cronjobsM.png" alt="" /> <span>{lang}wcf.acp.menu.link.cronjobs.view{/lang}</span></a></li>
			{if $action == 'edit'}<li><a href="index.php?action=CronjobExecute&amp;cronjobID={@$cronjobID}{@SID_ARG_2ND}" title="{lang}wcf.acp.cronjob.execute{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/cronjobExecuteM.png" alt="" /> <span>{lang}wcf.acp.cronjob.execute{/lang}</span></a></li>{/if}
		</ul>
	</div>
</div>
<form method="post" action="index.php?form=Cronjob{$action|ucfirst}">
	<div class="border content">
		<div class="container-1">
			<fieldset>
				<legend>{lang}wcf.acp.cronjob.edit.data{/lang}</legend>
				
				<div class="formElement{if $errorField == 'className'} formError{/if}" id="classNameDiv">
					<div class="formFieldLabel">
						<label for="className">{lang}wcf.acp.cronjob.className{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" id="className" name="className" value="{$className}" />
						{if $errorField == 'className'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.acp.cronjob.error.empty{/lang}{/if}
								{if $errorType == 'doesNotExist'}{lang}wcf.acp.cronjob.error.doesNotExist{/lang}{/if}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc hidden" id="classNameHelpMessage">
						<p>{lang}wcf.acp.cronjob.className.description{/lang}</p>
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('className');
				//]]></script>
				
				<div class="formElement" id="descriptionDiv">
					<div class="formFieldLabel">
						<label for="description">{lang}wcf.acp.cronjob.description{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" id="description" name="description" value="{$description}" />
					</div>
					<div class="formFieldDesc hidden" id="descriptionHelpMessage">
						<p>{lang}wcf.acp.cronjob.description.description{/lang}</p>
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('description');
				//]]></script>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.cronjob.edit.timing{/lang}</legend>
				<div class="formElement{if $errorField == 'startMinute'} formError{/if}" id="startMinuteDiv">
					<div class="formFieldLabel">
						<label for="startMinute">{lang}wcf.acp.cronjob.startMinute{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" id="startMinute" name="startMinute" value="{$startMinute}" />
						{if $errorField == 'startMinute'}
							<p class="innerError">
								{if $errorType == 'notValid'}{lang}wcf.acp.cronjob.error.notValid{/lang}{/if}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc hidden" id="startMinuteHelpMessage">
						<p>{lang}wcf.acp.cronjob.startMinute.description{/lang}</p>
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('startMinute');
				//]]></script>
				
				<div class="formElement{if $errorField == 'startHour'} formError{/if}" id="startHourDiv">
					<div class="formFieldLabel">
						<label for="startHour">{lang}wcf.acp.cronjob.startHour{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" id="startHour" name="startHour" value="{$startHour}" />
						{if $errorField == 'startHour'}
							<p class="innerError">
								{if $errorType == 'notValid'}{lang}wcf.acp.cronjob.error.notValid{/lang}{/if}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc hidden" id="startHourHelpMessage">
						<p>{lang}wcf.acp.cronjob.startHour.description{/lang}</p>
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('startHour');
				//]]></script>
				
				<div class="formElement{if $errorField == 'startDom'} formError{/if}" id="startDomDiv">
					<div class="formFieldLabel">
						<label for="startDom">{lang}wcf.acp.cronjob.startDom{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" id="startDom" name="startDom" value="{$startDom}" />
						{if $errorField == 'startDom'}
							<p class="innerError">
								{if $errorType == 'notValid'}{lang}wcf.acp.cronjob.error.notValid{/lang}{/if}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc hidden" id="startDomHelpMessage">
						<p>{lang}wcf.acp.cronjob.startDom.description{/lang}</p>
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('startDom');
				//]]></script>
				
				<div class="formElement{if $errorField == 'startMonth'} formError{/if}" id="startMonthDiv">
					<div class="formFieldLabel">
						<label for="startMonth">{lang}wcf.acp.cronjob.startMonth{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" id="startMonth" name="startMonth" value="{$startMonth}" />
						{if $errorField == 'startMonth'}
							<p class="innerError">
								{if $errorType == 'notValid'}{lang}wcf.acp.cronjob.error.notValid{/lang}{/if}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc hidden" id="startMonthHelpMessage">
						<p>{lang}wcf.acp.cronjob.startMonth.description{/lang}</p>
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('startMonth');
				//]]></script>
				
				<div class="formElement{if $errorField == 'startDow'} formError{/if}" id="startDowDiv">
					<div class="formFieldLabel">
						<label for="startDow">{lang}wcf.acp.cronjob.startDow{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" id="startDow" name="startDow" value="{$startDow}" />
						{if $errorField == 'startDow'}
							<p class="innerError">
								{if $errorType == 'notValid'}{lang}wcf.acp.cronjob.error.notValid{/lang}{/if}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc hidden" id="startDowHelpMessage">
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
		<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
		<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
		{@SID_INPUT_TAG}
 		{if $cronjobID|isset}<input type="hidden" name="cronjobID" value="{@$cronjobID}" />{/if}
	</div>
</form>

{include file='footer'}