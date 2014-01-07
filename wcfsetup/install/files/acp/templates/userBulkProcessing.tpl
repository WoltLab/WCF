{include file='header' pageTitle='wcf.acp.user.bulkProcessing'}

{if $mailID|isset}
	<script data-relocate="true">
		//<![CDATA[
		$(function() {
			WCF.Language.add('wcf.acp.worker.abort.confirmMessage', '{lang}wcf.acp.worker.abort.confirmMessage{/lang}');
			
			new WCF.ACP.Worker('mail', 'wcf\\system\\worker\\MailWorker', '', {
				mailID: {@$mailID}
			});
		});
		//]]>
	</script>
{/if}

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		function toggleContainer(value) {
			for (var $name in $targetContainers) {
				if ($name === value) {
					$targetContainers[$name].show();
				}
				else {
					$targetContainers[$name].hide();
				}
			}
		}
		
		var $targetContainers = { };
		$('input[name=action]').each(function(index, input) {
			var $input = $(input);
			var $value = $input.prop('value');
			
			if (!$targetContainers[$value]) {
				var $container = $('#' + $.wcfEscapeID($value + 'Div'));
				if ($container.length) {
					$targetContainers[$value] = $container;
				}
			}
			
			$input.change(function(event) {
				toggleContainer($(event.currentTarget).prop('value'));
			});
		});
		
		function setFileType(newType) {
			if (newType === 'csv') {
				$('#separatorDiv').show().next().show();
			}
			else {
				$('#separatorDiv').hide().next().hide();
			}
		}
		
		$('input[name=fileType]').each(function(index, input) {
			var $input = $(input);
			
			$input.change(function(event) {
				setFileType($input.prop('value'));
			});
		});
		
		toggleContainer('{@$action}');
		setFileType('{@$fileType}');
		
		new WCF.Search.User($('#username'), function(data) {
			$('#username').val(data.label);
			return false;
		}, false);
		WCF.TabMenu.init();
	});
	//]]>
</script>

<header class="boxHeadline">
	<h1>{lang}wcf.acp.user.bulkProcessing{/lang}</h1>
</header>

{include file='formError'}

<p class="warning">{lang}wcf.acp.user.bulkProcessing.warning{/lang}</p>

{if $affectedUsers|isset}
	<p class="success">{lang}wcf.acp.user.bulkProcessing.success{/lang}</p>
{/if}

<div class="contentNavigation">
	{hascontent}
		<nav>
			<ul>
				{content}
					{event name='contentNavigationButtons'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</div>

<form method="post" action="{link controller='UserBulkProcessing'}{/link}">
	<div class="tabMenuContainer" data-active="{$activeTabMenuItem}" data-store="activeTabMenuItem">
		<nav class="tabMenu">
			<ul>
				<li><a href="{@$__wcf->getAnchor('conditions')}">{lang}wcf.acp.user.search.conditions{/lang}</a></li>
				
				{if $options|count}
					<li><a href="{@$__wcf->getAnchor('profile')}">{lang}wcf.acp.user.search.conditions.profile{/lang}</a></li>
				{/if}
				
				<li><a href="{@$__wcf->getAnchor('action')}">{lang}wcf.acp.user.bulkProcessing.action{/lang}</a></li>
				{event name='tabMenuTabs'}
			</ul>
		</nav>
		
		<div id="conditions" class="container containerPadding tabMenuContent">
			<fieldset>
				<legend>{lang}wcf.acp.user.search.conditions{/lang}</legend>
				
				<dl>
					<dt><label for="username">{lang}wcf.user.username{/lang}</label></dt>
					<dd>
						<input type="text" id="username" name="username" value="{$username}" autofocus="autofocus" class="medium" />
					</dd>
				</dl>
				
				{if $__wcf->session->getPermission('admin.user.canEditMailAddress')}
					<dl>
						<dt><label for="email">{lang}wcf.user.email{/lang}</label></dt>
						<dd>
							<input type="text" id="email" name="email" value="{$email}" class="medium" />
						</dd>
					</dl>
				{/if}
				
				{if $availableGroups|count}
					<dl>
						<dt><label>{lang}wcf.acp.user.groups{/lang}</label></dt>
						<dd>
							{htmlCheckboxes options=$availableGroups name='groupIDs' selected=$groupIDs}
							
							<label class="marginTop"><input type="checkbox" name="invertGroupIDs" value="1" {if $invertGroupIDs == 1}checked="checked" {/if}/> {lang}wcf.acp.user.groups.invertSearch{/lang}</label>
						</dd>
					</dl>
				{/if}
				
				{if $availableLanguages|count > 1}
					<dl>
						<dt><label>{lang}wcf.user.language{/lang}</label></dt>
						<dd>
							{htmlCheckboxes options=$availableLanguages name='languageIDs' selected=$languageIDs disableEncoding=true}
						</dd>
					</dl>
				{/if}
				
				<dl>
					<dt><label for="registrationDateStart">{lang}wcf.user.registrationDate{/lang}</label></dt>
					<dd>
						<input type="date" id="registrationDateStart" name="registrationDateStart" value="{$registrationDateStart}" placeholder="{lang}wcf.date.period.start{/lang}" />
						<input type="date" id="registrationDateEnd" name="registrationDateEnd" value="{$registrationDateEnd}" placeholder="{lang}wcf.date.period.end{/lang}" />
					</dd>
				</dl>
				
				<dl>
					<dt><label for="lastActivityTimeStart">{lang}wcf.user.lastActivityTime{/lang}</label></dt>
					<dd>
						<input type="date" id="lastActivityTimeStart" name="lastActivityTimeStart" value="{$lastActivityTimeStart}" placeholder="{lang}wcf.date.period.start{/lang}" />
						<input type="date" id="lastActivityTimeEnd" name="lastActivityTimeEnd" value="{$lastActivityTimeEnd}" placeholder="{lang}wcf.date.period.end{/lang}" />
					</dd>
				</dl>
				
				{event name='conditionFields'}
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.user.search.conditions.states{/lang}</legend>
				
				<dl>
					<dt></dt>
					<dd>
						<label><input type="checkbox" name="banned" value="1" {if $banned == 1}checked="checked" {/if}/> {lang}wcf.acp.user.search.conditions.state.banned{/lang}</label>
						<label><input type="checkbox" name="notBanned" value="1" {if $notBanned == 1}checked="checked" {/if}/> {lang}wcf.acp.user.search.conditions.state.notBanned{/lang}</label>
						<label><input type="checkbox" name="enabled" value="1" {if $enabled == 1}checked="checked" {/if}/> {lang}wcf.acp.user.search.conditions.state.enabled{/lang}</label>
						<label><input type="checkbox" name="disabled" value="1" {if $disabled == 1}checked="checked" {/if}/> {lang}wcf.acp.user.search.conditions.state.disabled{/lang}</label>
						
						{event name='states'}
					</dd>
				</dl>
				
				{event name='stateFields'}
			</fieldset>
			
			{event name='conditionFieldsets'}
		</div>
		
		{if $options|count}
			<div id="profile" class="container containerPadding tabMenuContent">
				<fieldset>
					<legend>{lang}wcf.acp.user.search.conditions.profile{/lang}</legend>
					
					{include file='optionFieldList' langPrefix='wcf.user.option.'}
				</fieldset>
				
				{event name='profileFieldsets'}
			</div>
		{/if}
		
		<div id="action" class="container containerPadding tabMenuContent">
			<fieldset{if $errorField == 'action'} class="formError"{/if}>
				<legend>{lang}wcf.acp.user.bulkProcessing.action{/lang}</legend>
				
				<dl>
					<dt></dt>
					<dd>
						{if $__wcf->session->getPermission('admin.user.canMailUser')}
							<label><input type="radio" name="action" value="sendMail" {if $action == 'sendMail'}checked="checked" {/if}/> {lang}wcf.acp.user.sendMail{/lang}</label>
							<label><input type="radio" name="action" value="exportMailAddress" {if $action == 'exportMailAddress'}checked="checked" {/if}/> {lang}wcf.acp.user.exportEmailAddress{/lang}</label>
						{/if}
						{if $__wcf->session->getPermission('admin.user.canEditUser')}
							<label><input type="radio" name="action" value="assignToGroup" {if $action == 'assignToGroup'}checked="checked" {/if}/> {lang}wcf.acp.user.assignToGroup{/lang}</label>
						{/if}
						{if $__wcf->session->getPermission('admin.user.canDeleteUser')}
							<label><input type="radio" name="action" value="delete" {if $action == 'delete'}checked="checked" {/if}/> {lang}wcf.acp.user.delete{/lang}</label>
						{/if}
						
						{event name='actions'}
						
						{if $errorField == 'action'}
							<small class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.form.error.empty{/lang}{/if}
							</small>
						{/if}
					</dd>
				</dl>
			</fieldset>
			
			<div id="sendMailDiv">
				<fieldset>
					<legend>{lang}wcf.acp.user.sendMail.mail{/lang}</legend>
					
					<dl{if $errorField == 'subject'} class="formError"{/if}>
						<dt><label for="subject">{lang}wcf.acp.user.sendMail.subject{/lang}</label></dt>
						<dd>
							<input type="text" id="subject" name="subject" value="{$subject}" class="long" />
							{if $errorField == 'subject'}
								<small class="innerError">
									{if $errorType == 'empty'}{lang}wcf.global.form.error.empty{/lang}{/if}
								</small>
							{/if}
						</dd>
					</dl>
					
					<dl{if $errorField == 'from'} class="formError"{/if}>
						<dt><label for="from">{lang}wcf.acp.user.sendMail.from{/lang}</label></dt>
						<dd>
							<input type="text" id="from" name="from" value="{$from}" class="medium" />
							{if $errorField == 'from'}
								<small class="innerError">
									{if $errorType == 'empty'}{lang}wcf.global.form.error.empty{/lang}{/if}
								</small>
							{/if}
							<small>{lang}wcf.acp.user.sendMail.from.description{/lang}</small>
						</dd>
					</dl>
					
					<dl{if $errorField == 'text'} class="formError"{/if}>
						<dt><label for="text">{lang}wcf.acp.user.sendMail.text{/lang}</label></dt>
						<dd>
							<textarea id="text" name="text" rows="15" cols="40">{$text}</textarea>
							{if $errorField == 'text'}
								<small class="innerError" class="long">
									{if $errorType == 'empty'}{lang}wcf.global.form.error.empty{/lang}{/if}
								</small>
							{/if}
						</dd>
					</dl>
					
					<dl>
						<dt></dt>
						<dd>
							<label for="enableHTML"><input type="checkbox" id="enableHTML" name="enableHTML" value="1"{if $enableHTML == 1} checked="checked"{/if}/> {lang}wcf.acp.user.sendMail.enableHTML{/lang}</label>
						</dd>
					</dl>
				</fieldset>
			</div>
			
			<div id="exportMailAddressDiv">
				<fieldset>
					<legend>{lang}wcf.acp.user.exportEmailAddress.format{/lang}</legend>
					
					<dl>
						<dt><label>{lang}wcf.acp.user.exportEmailAddress.fileType{/lang}</label></dt>
						<dd>
							<label><input type="radio" name="fileType" value="csv" {if $fileType == 'csv'}checked="checked" {/if}/> {lang}wcf.acp.user.exportEmailAddress.fileType.csv{/lang}</label>
							<label><input type="radio" name="fileType" value="xml" {if $fileType == 'xml'}checked="checked" {/if}/> {lang}wcf.acp.user.exportEmailAddress.fileType.xml{/lang}</label>
						</dd>
					</dl>
					
					<dl id="separatorDiv">
						<dt><label for="separator">{lang}wcf.acp.user.exportEmailAddress.separator{/lang}</label></dt>
						<dd>
							<input type="text" id="separator" name="separator" value="{$separator}" class="medium" />
						</dd>
					</dl>
					
					<dl id="textSeparatorDiv">
						<dt><label for="textSeparator">{lang}wcf.acp.user.exportEmailAddress.textSeparator{/lang}</label></dt>
						<dd>
							<input type="text" id="textSeparator" name="textSeparator" value="{$textSeparator}" class="medium" />
						</dd>
					</dl>
				</fieldset>
			</div>
			
			<div id="assignToGroupDiv">
				<fieldset>
					<legend>{lang}wcf.acp.user.groups{/lang}</legend>
					
					<dl>
						<dt></dt>
						<dd{if $errorField == 'assignToGroupIDs'} class="formError"{/if}>
							{htmlCheckboxes options=$availableGroups name=assignToGroupIDs selected=$assignToGroupIDs}
							{if $errorField == 'assignToGroupIDs'}
								<small class="innerError">
									{if $errorType == 'empty'}{lang}wcf.global.form.error.empty{/lang}{/if}
								</small>
							{/if}
						</dd>
					</dl>
				</fieldset>
			</div>
			
			{event name='actionFieldsets'}
		</div>
	</div>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

{include file='footer'}
