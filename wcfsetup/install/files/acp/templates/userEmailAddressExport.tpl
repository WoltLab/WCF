{include file='header'}

<script type="text/javascript">
	//<![CDATA[
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
	<img src="{@RELATIVE_WCF_DIR}icon/usersL.png" alt="" />
	<hgroup>
		<h1>{lang}wcf.acp.user.exportEmailAddress{/lang}</h1>
	</hgroup>
</header>

<form method="post" action="index.php?form=UserEmailAddressExport">

	<div class="border content">
		<div class="container-1">
			<fieldset>
				<legend>{lang}wcf.acp.user.exportEmailAddress.markedUsers{/lang}</legend>
				
				<div>
					{implode from=$users item=$user}<a href="index.php?form=UserEdit&amp;userID={@$user->userID}{@SID_ARG_2ND}">{$user}</a>{/implode}
				</div>
			</fieldset>	
			
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
										<li><label><input type="radio" onclick="if (IS_SAFARI) setFileType('csv')" onfocus="setFileType('csv')" name="fileType" value="csv" {if $fileType == 'csv'}checked="checked" {/if}/> {lang}wcf.acp.user.exportEmailAddress.fileType.csv{/lang}</label></li>
										<li><label><input type="radio" onclick="if (IS_SAFARI) setFileType('xml')" onfocus="setFileType('xml')" name="fileType" value="xml" {if $fileType == 'xml'}checked="checked" {/if}/> {lang}wcf.acp.user.exportEmailAddress.fileType.xml{/lang}</label></li>
									</ul>
								</div>
							</fieldset>
						</div>
					</div>
				
					<div id="separatorDiv" class="formElement">
						<div class="formFieldLabel">
							<label for="separator">{lang}wcf.acp.user.exportEmailAddress.separator{/lang}</label>
						</div>
						<div class="formField">
							<textarea id="separator" name="separator" rows="2" cols="40">{$separator}</textarea>
						</div>
					</div>
					
					<div id="textSeparatorDiv" class="formElement">
						<div class="formFieldLabel">
							<label for="textSeparator">{lang}wcf.acp.user.exportEmailAddress.textSeparator{/lang}</label>
						</div>
						<div class="formField">
							<input type="text" id="textSeparator" name="textSeparator" value="{$textSeparator}" class="inputText" />
						</div>
					</div>
				</div>
			</fieldset>
	
		</div>
	</div>
	
	<div class="formSubmit">
		<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
		<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
		{@SID_INPUT_TAG}
 		<input type="hidden" name="userIDs" value="{@$userIDs}" />
 	</div>
</form>

{include file='footer'}
