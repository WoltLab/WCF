{include file='header'}

<script type="text/javascript">
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
	<hgroup>
		<h1>{lang}wcf.acp.user.massProcessing{/lang}</h1>
	</hgroup>
</header>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

<p class="warning">{lang}wcf.acp.user.massProcessing.warning{/lang}</p>

{if $affectedUsers|isset}
	<p class="success">{lang}wcf.acp.user.massProcessing.success{/lang}</p>	
{/if}

<form method="post" action="{link controller='UsersMassProcessing'}{/link}">
	<div class="container containerPadding marginTop shadow">
		<fieldset>
			<legend>{lang}wcf.acp.user.search.conditions.general{/lang}</legend>
			
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
						<input type="email" id="email" name="email" value="{$email}" class="medium" />
					</dd>
				</dl>
			{/if}
			
			{if $availableGroups|count}
				<dl>
					<dt><label>{lang}wcf.acp.user.groups{/lang}</label></dt>
					<dd>
						{htmlCheckboxes options=$availableGroups name='groupIDArray' selected=$groupIDArray}
									
						<!-- ToDo --><label style="margin-top: 10px"><input type="checkbox" name="invertGroupIDs" value="1" {if $invertGroupIDs == 1}checked="checked" {/if}/> {lang}wcf.acp.user.groups.invertSearch{/lang}</label>
					</dd>
				</dl>
			{/if}
			
			{if $availableLanguages|count > 1}
				<dl>
					<dt><label>{lang}wcf.user.language{/lang}</label></dt>
					<dd>
						{htmlCheckboxes options=$availableLanguages name='languageIDArray' selected=$languageIDArray disableEncoding=true}
					</dd>
				</dl>
			{/if}
		</fieldset>
	
		{event name='fieldsets'}
		
		{hascontent}
			<div class="tabMenuContainer">
				<nav class="tabMenu">
					<ul>
						{content}
							{if $options|count}
								<li><a href="#profile">{lang}wcf.acp.user.search.conditions.profile{/lang}</a></li>
							{/if}
	
							{event name='tabMenuTabs'}
						{/content}
					</ul>
				</nav>
				
				{if $options|count}
					<fieldset id="profile" class="container containerPadding tabMenuContent hidden">
						{include file='optionFieldList' langPrefix='wcf.user.option.'}
					</fieldset>
				{/if}
				
				{event name='tabMenuContent'}
			</div>
		{/hascontent}
	</div>
	
	<div class="container containerPadding marginTop shadow">
		<fieldset{if $errorField == 'action'} class="formError"{/if}>
			<legend>{lang}wcf.acp.user.massProcessing.action{/lang}</legend>
			
			<dl>
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
					
					{event name='additionalActions'}
					
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
				
				<dl{if $errorField == 'from'} class="formError"{/if}>
					<dt><label for="from">{lang}wcf.acp.user.sendMail.from{/lang}</label></dt>
					<dd>
						<input type="email" id="from" name="from" value="{$from}" class="medium" />
						{if $errorField == 'from'}
							<small class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.form.error.empty{/lang}{/if}
							</small>
						{/if}
						<small>{lang}wcf.acp.user.sendMail.from.description{/lang}</small>
					</dd>
				</dl>
				
				<dl{if $errorField == 'subject'} class="formError"{/if}>
					<dt><label for="subject">{lang}wcf.acp.user.sendMail.subject{/lang}</label></dt>
					<dd>
						<input type="text" id="subject" name="subject" value="{$subject}" class="long" />
						{if $errorField == 'subject'}
							<small class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.form.error.empty{/lang}{/if}
							</small>
						{/if}
						<small>{lang}wcf.acp.user.sendMail.subject.description{/lang}</small>
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
						<small>{lang}wcf.acp.user.sendMail.text.description{/lang}</small>
					</dd>
				</dl>
				
				<dl>
					<dd>
						<label for="enableHTML"><input type="checkbox" id="enableHTML" name="enableHTML" value="1"{if $enableHTML == 1} checked="checked"{/if}/> {lang}wcf.acp.user.sendMail.enableHTML{/lang}</label>
					</dd>
				</dl>
			</div>
		</fieldset>
		
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
					<dd{if $errorField == 'assignToGroupIDArray'} class="formError"{/if}>
						{htmlCheckboxes options=$availableGroups name=assignToGroupIDArray selected=$assignToGroupIDArray}
						{if $errorField == 'assignToGroupIDArray'}
							<small class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.form.error.empty{/lang}{/if}
							</small>
						{/if}
					</dd>
				</dl>
			</fieldset>
		</div>
		
		{if $additionalActionSettings|isset}{@$additionalActionSettings}{/if}
	</div>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
 	</div>
</form>

{include file='footer'}
