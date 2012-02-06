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

<header class="wcf-mainHeading">
	<img src="{@RELATIVE_WCF_DIR}icon/user1.svg" alt="" />
	<hgroup>
		<h1>{lang}wcf.acp.user.massProcessing{/lang}</h1>
	</hgroup>
</header>

{if $errorField}
	<p class="wcf-error">{lang}wcf.global.form.error{/lang}</p>
{/if}

<p class="wcf-warning">{lang}wcf.acp.user.massProcessing.warning{/lang}</p>

{if $affectedUsers|isset}
	<p class="wcf-success">{lang}wcf.acp.user.massProcessing.success{/lang}</p>	
{/if}

<form method="post" action="{link controller='UsersMassProcessing'}{/link}">
	<div class="wcf-border wcf-content">
		
		<hgroup class="wcf-subHeading">
			<h1>{lang}wcf.acp.user.massProcessing.conditions{/lang}</h1>
		</hgroup>
		
		<fieldset>
			<legend>{lang}wcf.acp.user.search.conditions.general{/lang}</legend>
			
			<dl>
				<dt><label for="username">{lang}wcf.user.username{/lang}</label></dt>
				<dd>
					<input type="text" id="username" name="username" value="{$username}" class="medium" />
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
						<fieldset>
							<legend>{lang}wcf.acp.user.groups{/lang}</legend>
							<dl>
								<dd>
									{htmlCheckboxes options=$availableGroups name='groupIDArray' selected=$groupIDArray}
									
									<!-- ToDo --><label style="margin-top: 10px"><input type="checkbox" name="invertGroupIDs" value="1" {if $invertGroupIDs == 1}checked="checked" {/if}/> {lang}wcf.acp.user.groups.invertSearch{/lang}</label>
								</dd>
							</dl>
						</fieldset>
					</dd>
				</dl>
			{/if}
			
			{if $availableLanguages|count > 1}
				<dl>
					<dt><label>{lang}wcf.user.language{/lang}</label></dt>
					<dd>
						<fieldset>
							<legend>{lang}wcf.acp.user.language{/lang}</legend>
							
							<dl>
								<dd>{htmlCheckboxes options=$availableLanguages name='languageIDArray' selected=$languageIDArray disableEncoding=true}</dd>
							</dl>
						</fieldset>
					</dd>
				</dl>
			{/if}
		</fieldset>
	
		{event name='fieldsets'}
		
		{hascontent}
			<div class="wcf-tabMenuContainer">
				<nav class="wcf-tabMenu">
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
					<div id="profile" class="wcf-border tabMenuContent hidden">
						<hgroup class="wcf-subHeading">
							<h1>{lang}wcf.acp.user.search.conditions.profile{/lang}</h1>
						</hgroup>
						
						{include file='optionFieldList' langPrefix='wcf.user.option.'}
					</div>
				{/if}
				
				{event name='tabMenuContent'}
			</div>
		{/hascontent}
	</div>
	
	<div class="wcf-border wcf-content">
		<hgroup class="wcf-subHeading">
			<h1>{lang}wcf.acp.user.massProcessing.action{/lang}</h1>
		</hgroup>
			
		<dl{if $errorField == 'action'} class="wcf-formError"{/if}>
			<dt><label>{lang}wcf.acp.user.massProcessing.action{/lang}</label></dt>
			<dd>
				<fieldset>
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
								<small class="wcf-innerError">
									{if $errorType == 'empty'}{lang}wcf.global.form.error.empty{/lang}{/if}
								</small>
							{/if}
						</dd>
					</dl>
				</fieldset>
			</dd>
		</dl>
		
		<div id="sendMailDiv">
			<fieldset>
				<legend>{lang}wcf.acp.user.sendMail.mail{/lang}</legend>
				
				<dl{if $errorField == 'from'} class="wcf-formError"{/if}>
					<dt><label for="from">{lang}wcf.acp.user.sendMail.from{/lang}</label></dt>
					<dd>
						<input type="email" id="from" name="from" value="{$from}" class="medium" />
						{if $errorField == 'from'}
							<small class="wcf-innerError">
								{if $errorType == 'empty'}{lang}wcf.global.form.error.empty{/lang}{/if}
							</small>
						{/if}
						<small>{lang}wcf.acp.user.sendMail.from.description{/lang}</small>
					</dd>
				</dl>
				
				<dl{if $errorField == 'subject'} class="wcf-formError"{/if}>
					<dt><label for="subject">{lang}wcf.acp.user.sendMail.subject{/lang}</label></dt>
					<dd>
						<input type="text" id="subject" name="subject" value="{$subject}" class="long" />
						{if $errorField == 'subject'}
							<small class="wcf-innerError">
								{if $errorType == 'empty'}{lang}wcf.global.form.error.empty{/lang}{/if}
							</small>
						{/if}
						<small>{lang}wcf.acp.user.sendMail.subject.description{/lang}</small>
					</dd>
				</dl>
				
				<dl{if $errorField == 'text'} class="wcf-formError"{/if}>
					<dt><label for="text">{lang}wcf.acp.user.sendMail.text{/lang}</label></dt>
					<dd>
						<textarea id="text" name="text" rows="15" cols="40">{$text}</textarea>
						{if $errorField == 'text'}
							<small class="wcf-innerError" class="long">
								{if $errorType == 'empty'}{lang}wcf.global.form.error.empty{/lang}{/if}
							</small>
						{/if}
						<small>{lang}wcf.acp.user.sendMail.text.description{/lang}</small>
					</dd>
				</dl>
				
				<dl>
					<dt class="reversed"><label for="enableHTML">{lang}wcf.acp.user.sendMail.enableHTML{/lang}</label></dt>
					<dd>
						<input type="checkbox" id="enableHTML" name="enableHTML" value="1"{if $enableHTML == 1} checked="checked"{/if}/> 
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
						<fieldset>
							<legend>{lang}wcf.acp.user.exportEmailAddress.fileType{/lang}</legend>
							
							<dl>
								<dd><label><input type="radio" name="fileType" value="csv" {if $fileType == 'csv'}checked="checked" {/if}/> {lang}wcf.acp.user.exportEmailAddress.fileType.csv{/lang}</label></dd>
								<dd><label><input type="radio" name="fileType" value="xml" {if $fileType == 'xml'}checked="checked" {/if}/> {lang}wcf.acp.user.exportEmailAddress.fileType.xml{/lang}</label></dd>
							</dl>
						</fieldset>
					</dd>
				</dl>
			
				<dl id="separatorDiv">
					<dt><label for="separator">{lang}wcf.acp.user.exportEmailAddress.separator{/lang}</label></dt>
					<dd>
						<textarea id="separator" name="separator" rows="2" cols="40">{$separator}</textarea>
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
					<dd{if $errorField == 'assignToGroupIDArray'} class="wcf-formError"{/if}>
						{htmlCheckboxes options=$availableGroups name=assignToGroupIDArray selected=$assignToGroupIDArray}
						{if $errorField == 'assignToGroupIDArray'}
							<small class="wcf-innerError">
								{if $errorType == 'empty'}{lang}wcf.global.form.error.empty{/lang}{/if}
							</small>
						{/if}
					</dd>
				</dl>
			</fieldset>
		</div>
		
		{if $additionalActionSettings|isset}{@$additionalActionSettings}{/if}
	</div>
	
	<div class="wcf-formSubmit">
		<input type="reset" value="{lang}wcf.global.button.reset{/lang}" accesskey="r" />
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		{@SID_INPUT_TAG}
 	</div>
</form>

{include file='footer'}
