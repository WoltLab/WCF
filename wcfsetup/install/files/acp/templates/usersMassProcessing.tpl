{include file='header'}

<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/Suggestion.class.js"></script>
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/TabMenu.class.js"></script>
<script type="text/javascript">
	//<![CDATA[
	// disable
	function disableAll() {
		{foreach from=$availableActions item=availableAction}
		disable{@$availableAction|ucfirst}();
		{/foreach}
	}
	
	function disableSendMail() {
		hideOptions('sendMailDiv');
	}
	
	function disableExportMailAddress() {
		hideOptions('exportMailAddressDiv');
	}
	
	function disableAssignToGroup() {
		hideOptions('assignToGroupDiv');
	}
	
	function disableDelete() { }
	
	// enable
	function enableSendMail() {
		disableAll();
		showOptions('sendMailDiv');
	}
	
	function enableExportMailAddress() {
		disableAll();
		showOptions('exportMailAddressDiv');
	}
	
	function enableAssignToGroup() {
		disableAll();
		showOptions('assignToGroupDiv');
	}
	
	function enableDelete() {
		disableAll();
	}
	
	var tabMenu = new TabMenu();
	onloadEvents.push(function() {
		tabMenu.showSubTabMenu('profile')
		{if $action != ''}enable{@$action|ucfirst}();{else}disableAll();{/if}
	});
	
	function setFileType(newType) {
		switch (newType) {
			case 'csv':
				showOptions('separatorDiv', 'textSeparatorDiv');
				break;
			case 'xml':
				hideOptions('separatorDiv', 'textSeparatorDiv');
				break;
		}
	}
	onloadEvents.push(function() { setFileType('{@$fileType}'); });
	//]]>
</script>

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/usersMassProcessingL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.user.massProcessing{/lang}</h2>
	</div>
</div>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $affectedUsers|isset}
	<p class="success">{lang}wcf.acp.user.massProcessing.success{/lang}</p>	
{/if}

<p class="warning">{lang}wcf.acp.user.massProcessing.warning{/lang}</p>

<form method="post" action="index.php?form=UsersMassProcessing">
	<div class="border content">
		<div class="container-1">
			<h3 class="subHeadline">{lang}wcf.acp.user.massProcessing.conditions{/lang}</h3>
			
			<fieldset>
				<legend>{lang}wcf.acp.user.search.conditions.general{/lang}</legend>
				
				<div class="formElement">
					<div class="formFieldLabel">
						<label for="username">{lang}wcf.user.username{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" id="username" name="username" value="{$username}" />
						<script type="text/javascript">
							//<![CDATA[
							suggestion.enableMultiple(false);
							suggestion.init('username');
							//]]>
						</script>
					</div>
				</div>
				
				{if $__wcf->session->getPermission('admin.user.canEditMailAddress')}
					<div class="formElement">
						<div class="formFieldLabel">
							<label for="email">{lang}wcf.user.email{/lang}</label>
						</div>
						<div class="formField">	
							<input type="text" class="inputText" id="email" name="email" value="{$email}" />
						</div>
					</div>
				{/if}
				
				{if $availableGroups|count}
					<div class="formGroup">
						<div class="formGroupLabel">
							<label>{lang}wcf.acp.user.groups{/lang}</label>
						</div>
						<div class="formGroupField">
							<fieldset>
								<legend>{lang}wcf.acp.user.groups{/lang}</legend>
								
								<div class="formField">
									{htmlCheckboxes options=$availableGroups name='groupIDArray' selected=$groupIDArray}
									
									<label style="margin-top: 10px"><input type="checkbox" name="invertGroupIDs" value="1" {if $invertGroupIDs == 1}checked="checked" {/if}/> {lang}wcf.acp.user.groups.invertSearch{/lang}</label>
								</div>
							</fieldset>
						</div>
					</div>
				{/if}
				
				{if $availableLanguages|count > 1}
					<div class="formGroup">
						<div class="formGroupLabel">
							<label>{lang}wcf.user.language{/lang}</label>
						</div>
						<div class="formGroupField">
							<fieldset>
								<legend>{lang}wcf.acp.user.language{/lang}</legend>
								
								<div class="formField">
									{htmlCheckboxes options=$availableLanguages name='languageIDArray' selected=$languageIDArray disableEncoding=true}
								</div>
							</fieldset>
						</div>
					</div>
				{/if}
			</fieldset>
		
			{if $additionalFields|isset}{@$additionalFields}{/if}
			
			<div class="tabMenu">
				<ul>
					{if $options|count}<li id="profile"><a onclick="tabMenu.showSubTabMenu('profile');"><span>{lang}wcf.acp.user.search.conditions.profile{/lang}</span></a></li>{/if}
					{if $additionalTabs|isset}{@$additionalTabs}{/if}
				</ul>
			</div>
			<div class="subTabMenu">
				<div class="containerHead"><div> </div></div>
			</div>
			
			{if $options|count}
				<div class="border tabMenuContent hidden" id="profile-content">
					<div class="container-1">
						<h3 class="subHeadline">{lang}wcf.acp.user.search.conditions.profile{/lang}</h3>
						{include file='optionFieldList' langPrefix='wcf.user.option.'}
					</div>
				</div>
			{/if}
			
			{if $additionalTabContents|isset}{@$additionalTabContents}{/if}
		</div>
	</div>
	<div class="border content">
		<div class="container-1">
			<h3 class="subHeadline">{lang}wcf.acp.user.massProcessing.action{/lang}</h3>
				
			<div class="formGroup{if $errorField == 'action'} formError{/if}">
				<div class="formGroupLabel">
					<label>{lang}wcf.acp.user.massProcessing.action{/lang}</label>
				</div>
				<div class="formGroupField">
					<fieldset>
						<legend>{lang}wcf.acp.user.massProcessing.action{/lang}</legend>
						<div class="formField">
							<ul class="formOptionsLong">
								{if $__wcf->session->getPermission('admin.user.canMailUser')}
									<li><label><input onclick="if (IS_SAFARI) enableSendMail()" onfocus="enableSendMail()" type="radio" name="action" value="sendMail" {if $action == 'sendMail'}checked="checked" {/if}/> {lang}wcf.acp.user.sendMail{/lang}</label></li>
									<li><label><input onclick="if (IS_SAFARI) enableExportMailAddress()" onfocus="enableExportMailAddress()" type="radio" name="action" value="exportMailAddress" {if $action == 'exportMailAddress'}checked="checked" {/if}/> {lang}wcf.acp.user.exportEmailAddress{/lang}</label></li>
								{/if}
								{if $__wcf->session->getPermission('admin.user.canEditUser')}
									<li><label><input onclick="if (IS_SAFARI) enableAssignToGroup()" onfocus="enableAssignToGroup()" type="radio" name="action" value="assignToGroup" {if $action == 'assignToGroup'}checked="checked" {/if}/> {lang}wcf.acp.user.assignToGroup{/lang}</label></li>
								{/if}
								{if $__wcf->session->getPermission('admin.user.canDeleteUser')}
									<li><label><input onclick="if (IS_SAFARI) enableDelete()" onfocus="enableDelete()" type="radio" name="action" value="delete" {if $action == 'delete'}checked="checked" {/if}/> {lang}wcf.acp.user.delete{/lang}</label></li>
								{/if}
								{if $additionalActions|isset}{@$additionalActions}{/if}
							</ul>
						</div>
						{if $errorField == 'action'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
							</p>
						{/if}
					</fieldset>
				</div>
			</div>
			
			<div id="sendMailDiv">
				<fieldset>
					<legend>{lang}wcf.acp.user.sendMail.mail{/lang}</legend>
					
					<div>
						<div class="formElement{if $errorField == 'subject'} formError{/if}" id="subjectDiv">
							<div class="formFieldLabel">
								<label for="subject">{lang}wcf.acp.user.sendMail.subject{/lang}</label>
							</div>
							<div class="formField">
								<input type="text" class="inputText" id="subject" name="subject" value="{$subject}" />
								{if $errorField == 'subject'}
									<p class="innerError">
										{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
									</p>
								{/if}
							</div>
							<div class="formFieldDesc hidden" id="subjectHelpMessage">
								<p>{lang}wcf.acp.user.sendMail.subject.description{/lang}</p>
							</div>
						</div>
						<script type="text/javascript">//<![CDATA[
							inlineHelp.register('subject');
						//]]></script>
						
						<div class="formElement{if $errorField == 'from'} formError{/if}" id="fromDiv">
							<div class="formFieldLabel">
								<label for="from">{lang}wcf.acp.user.sendMail.from{/lang}</label>
							</div>
							<div class="formField">
								<input type="text" class="inputText" id="from" name="from" value="{$from}" />
								{if $errorField == 'from'}
									<p class="innerError">
										{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
									</p>
								{/if}
							</div>
							<div class="formFieldDesc hidden" id="fromHelpMessage">
								{lang}wcf.acp.user.sendMail.from.description{/lang}
							</div>
						</div>
						<script type="text/javascript">//<![CDATA[
							inlineHelp.register('from');
						//]]></script>
					
						<div class="formElement{if $errorField == 'text'} formError{/if}" id="textDiv">
							<div class="formFieldLabel">
								<label for="text">{lang}wcf.acp.user.sendMail.text{/lang}</label>
							</div>
							<div class="formField">
								<textarea id="text" name="text" rows="15" cols="40">{$text}</textarea>
								{if $errorField == 'text'}
									<p class="innerError">
										{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
									</p>
								{/if}
							</div>
							<div class="formFieldDesc hidden" id="textHelpMessage">
								<p>{lang}wcf.acp.user.sendMail.text.description{/lang}</p>
							</div>
						</div>
						<script type="text/javascript">//<![CDATA[
							inlineHelp.register('text');
						//]]></script>
						
						<div class="formElement">
							<div class="formField">
								<label><input type="checkbox" name="enableHTML" id="enableHTML" value="1" {if $enableHTML == 1}checked="checked" {/if}/> {lang}wcf.acp.user.sendMail.enableHTML{/lang}</label>
							</div>
						</div>
					</div>
				</fieldset>
			</div>
			
			<div id="exportMailAddressDiv">
				<fieldset>
					<legend>{lang}wcf.acp.user.exportEmailAddress.format{/lang}</legend>
					
					<div>
						<div class="formGroup">
							<div class="formGroupLabel">
								<label>{lang}wcf.acp.user.exportEmailAddress.fileType{/lang}</label>
							</div>
							<div class="formGroupField">
								<fieldset>
									<legend>{lang}wcf.acp.user.exportEmailAddress.fileType{/lang}</legend>
									
									<div class="formField">
										<ul class="formOptionsLong">
											<li><label><input onclick="if (IS_SAFARI) setFileType('csv')" onfocus="setFileType('csv')" type="radio" name="fileType" value="csv" {if $fileType == 'csv'}checked="checked" {/if}/> {lang}wcf.acp.user.exportEmailAddress.fileType.csv{/lang}</label></li>
											<li><label><input onclick="if (IS_SAFARI) setFileType('xml')" onfocus="setFileType('xml')" type="radio" name="fileType" value="xml" {if $fileType == 'xml'}checked="checked" {/if}/> {lang}wcf.acp.user.exportEmailAddress.fileType.xml{/lang}</label></li>
										</ul>
									</div>
								</fieldset>
							</div>
						</div>
					
						<div class="formElement" id="separatorDiv">
							<div class="formFieldLabel">
								<label for="separator">{lang}wcf.acp.user.exportEmailAddress.separator{/lang}</label>
							</div>
							<div class="formField">
								<textarea id="separator" name="separator" rows="2" cols="40">{$separator}</textarea>
							</div>
						</div>
						
						<div class="formElement" id="textSeparatorDiv">
							<div class="formFieldLabel">
								<label for="textSeparator">{lang}wcf.acp.user.exportEmailAddress.textSeparator{/lang}</label>
							</div>
							<div class="formField">
								<input type="text" class="inputText" id="textSeparator" name="textSeparator" value="{$textSeparator}" />
							</div>
						</div>
					</div>
				</fieldset>
			</div>
			
			<div id="assignToGroupDiv">
				<fieldset>
					<legend>{lang}wcf.acp.user.groups{/lang}</legend>
					
					<div>
						<div class="formField{if $errorField == 'assignToGroupIDArray'} formError{/if}">
							{htmlCheckboxes options=$availableGroups name=assignToGroupIDArray selected=$assignToGroupIDArray}
							{if $errorField == 'assignToGroupIDArray'}
								<p class="innerError">
									{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								</p>
							{/if}
						</div>
					</div>
				</fieldset>
			</div>
			
			{if $additionalActionSettings|isset}{@$additionalActionSettings}{/if}
		</div>
	</div>
	
	<div class="formSubmit">
		<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
		<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
		{@SID_INPUT_TAG}
 	</div>
</form>

{include file='footer'}