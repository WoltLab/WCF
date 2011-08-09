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

<header class="mainHeading">
	<img src="{@RELATIVE_WCF_DIR}icon/usersMassProcessingL.png" alt="" />
	<hgroup>
		<h1>{lang}wcf.acp.user.massProcessing{/lang}</h1>
	</hgroup>
</header>

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
			<h3 class="subHeading">{lang}wcf.acp.user.massProcessing.conditions{/lang}</h3>
			
			<fieldset>
				<legend>{lang}wcf.acp.user.search.conditions.general{/lang}</legend>
				
				<dl>
					<dt><label for="username">{lang}wcf.user.username{/lang}</label></dt>
					<dd>
						<input type="text" id="username" name="username" value="{$username}" class="medium" />
						<script type="text/javascript">
							//<![CDATA[
							suggestion.enableMultiple(false);
							suggestion.init('username');
							//]]>
						</script>
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
										
										<label style="margin-top: 10px"><input type="checkbox" name="invertGroupIDs" value="1" {if $invertGroupIDs == 1}checked="checked" {/if}/> {lang}wcf.acp.user.groups.invertSearch{/lang}</label>
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
				<div id="profile-content" class="border tabMenuContent hidden">
					<div class="container-1">
						<h1 class="subHeading">{lang}wcf.acp.user.search.conditions.profile{/lang}</h1>
						
						{include file='optionFieldList' langPrefix='wcf.user.option.'}
					</div>
				</div>
			{/if}
			
			{if $additionalTabContents|isset}{@$additionalTabContents}{/if}
		</div>
	</div>
	
	<div class="border content">
		<div class="container-1">
			<h1 class="subHeading">{lang}wcf.acp.user.massProcessing.action{/lang}</h1>
				
			<dl{if $errorField == 'action'} class="formError"{/if}>
				<dt><label>{lang}wcf.acp.user.massProcessing.action{/lang}</label></dt>
				<dd>
					<fieldset>
						<legend>{lang}wcf.acp.user.massProcessing.action{/lang}</legend>
						
						<dl>
							<dd>
								{if $__wcf->session->getPermission('admin.user.canMailUser')}
									<label><input type="radio" onclick="if (IS_SAFARI) enableSendMail()" onfocus="enableSendMail()" name="action" value="sendMail" {if $action == 'sendMail'}checked="checked" {/if}/> {lang}wcf.acp.user.sendMail{/lang}</label>
									<label><input type="radio" onclick="if (IS_SAFARI) enableExportMailAddress()" onfocus="enableExportMailAddress()" name="action" value="exportMailAddress" {if $action == 'exportMailAddress'}checked="checked" {/if}/> {lang}wcf.acp.user.exportEmailAddress{/lang}</label>
								{/if}
								{if $__wcf->session->getPermission('admin.user.canEditUser')}
									<label><input type="radio" onclick="if (IS_SAFARI) enableAssignToGroup()" onfocus="enableAssignToGroup()" name="action" value="assignToGroup" {if $action == 'assignToGroup'}checked="checked" {/if}/> {lang}wcf.acp.user.assignToGroup{/lang}</label>
								{/if}
								{if $__wcf->session->getPermission('admin.user.canDeleteUser')}
									<label><input type="radio" onclick="if (IS_SAFARI) enableDelete()" onfocus="enableDelete()" name="action" value="delete" {if $action == 'delete'}checked="checked" {/if}/> {lang}wcf.acp.user.delete{/lang}</label>
								{/if}
								
								{if $additionalActions|isset}{@$additionalActions}{/if}
								
								{if $errorField == 'action'}
									<small class="innerError">
										{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
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
					
					<dl id="fromDiv"{if $errorField == 'from'} class="formError"{/if}>
						<dt><label for="from">{lang}wcf.acp.user.sendMail.from{/lang}</label></dt>
						<dd>
							<input type="email" id="from" name="from" value="{$from}" class="medium" />
							{if $errorField == 'from'}
								<small class="innerError">
									{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								</small>
							{/if}
							<small id="fromHelpMessage">{lang}wcf.acp.user.sendMail.from.description{/lang}</small>
						</dd>
					</dl>
					
					<dl id="subjectDiv"{if $errorField == 'subject'} class="formError"{/if}>
						<dt>
							<label for="subject">{lang}wcf.acp.user.sendMail.subject{/lang}</label>
						</dt>
						<dd>
							<input type="text" id="subject" name="subject" value="{$subject}" class="long" />
							{if $errorField == 'subject'}
								<small class="innerError">
									{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								</small>
							{/if}
							<small id="subjectHelpMessage">{lang}wcf.acp.user.sendMail.subject.description{/lang}</small>
						</dd>
					</dl>
					
					<dl id="textDiv"{if $errorField == 'text'} class="formError"{/if}>
						<dt><label for="text">{lang}wcf.acp.user.sendMail.text{/lang}</label></dt>
						<dd>
							<textarea id="text" name="text" rows="15" cols="40">{$text}</textarea>
							{if $errorField == 'text'}
								<small class="innerError" class="long">
									{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								</small>
							{/if}
							<small id="textHelpMessage">{lang}wcf.acp.user.sendMail.text.description{/lang}</small>
						</dd>
					</dl>
					
					<dl class="reversed separated">
						<dt><label for="enableHTML">{lang}wcf.acp.user.sendMail.enableHTML{/lang}</label></dt>
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
									<dd><label><input type="radio" onclick="if (IS_SAFARI) setFileType('csv')" onfocus="setFileType('csv')" name="fileType" value="csv" {if $fileType == 'csv'}checked="checked" {/if}/> {lang}wcf.acp.user.exportEmailAddress.fileType.csv{/lang}</label></dd>
									<dd><label><input type="radio" onclick="if (IS_SAFARI) setFileType('xml')" onfocus="setFileType('xml')" name="fileType" value="xml" {if $fileType == 'xml'}checked="checked" {/if}/> {lang}wcf.acp.user.exportEmailAddress.fileType.xml{/lang}</label></dd>
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
						<dd{if $errorField == 'assignToGroupIDArray'} class="formError"{/if}>
							{htmlCheckboxes options=$availableGroups name=assignToGroupIDArray selected=$assignToGroupIDArray}
							{if $errorField == 'assignToGroupIDArray'}
								<small class="innerError">
									{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								</small>
							{/if}
						</dd>
					</dl>
				</fieldset>
			</div>
			
			{if $additionalActionSettings|isset}{@$additionalActionSettings}{/if}
		</div>
	</div>
	
	<div class="formSubmit">
		<input type="reset" value="{lang}wcf.global.button.reset{/lang}" accesskey="r" />
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		{@SID_INPUT_TAG}
 	</div>
</form>

{include file='footer'}
