<div class="section">
	<dl>
		<dt><label for="aclAllowAll">{lang}wcf.acl.allowAll{/lang}</label></dt>
		<dd>
			<ol class="flexibleButtonGroup">
				<li>
					<input type="radio" id="aclAllowAll" name="aclValues[allowAll]" value="1"{if $aclValues[allowAll]} checked{/if}>
					<label for="aclAllowAll" class="green"><span class="icon icon16 fa-check"></span> {lang}wcf.acp.option.type.boolean.yes{/lang}</label>
				</li>
				<li>
					<input type="radio" id="aclAllowAll_no" name="aclValues[allowAll]" value="0"{if !$aclValues[allowAll]} checked{/if}>
					<label for="aclAllowAll_no" class="red"><span class="icon icon16 fa-times"></span> {lang}wcf.acp.option.type.boolean.no{/lang}</label>
				</li>
			</ol>
		</dd>
	</dl>
</div>

<section class="section" id="aclInputContainer"{if $aclValues[allowAll]} style="display: none;"{/if}>
	<h2 class="sectionTitle">{lang}wcf.acl.access{/lang}</h2>
	<dl>
		<dt><label for="aclSearchInput">{lang}wcf.acl.access.grant{/lang}</label></dt>
		<dd>
			<input type="text" id="aclSearchInput" class="long" placeholder="{lang}wcf.acl.search.description{/lang}">
		</dd>
	</dl>
	
	<dl id="aclListContainer"{if $aclValues[allowAll]} style="display: none;"{/if}>
		<dt>{lang}wcf.acl.access.granted{/lang}</dt>
		<dd>
			<ul id="aclAccessList" class="aclList containerList">
				{foreach from=$aclValues[group] item=aclGroup}
					<li>
						<span class="icon icon16 fa-users"></span>
						<span class="aclLabel">{$aclGroup}</span>
						<span class="icon icon16 fa-times pointer jsTooltip" title="{lang}wcf.global.button.delete{/lang}"></span>
						<input type="hidden" name="aclValues[group][]" value="{@$aclGroup->groupID}">
					</li>
				{/foreach}
				{foreach from=$aclValues[user] item=aclUser}
					<li>
						<span class="icon icon16 fa-user"></span>
						<span class="aclLabel">{$aclUser}</span>
						<span class="icon icon16 fa-times pointer jsTooltip" title="{lang}wcf.global.button.delete{/lang}"></span>
						<input type="hidden" name="aclValues[user][]" value="{@$aclUser->userID}">
					</li>
				{/foreach}
			</ul>
		</dd>
	</dl>
</section>

<script data-relocate="true">
	require(['WoltLab/WCF/Ui/Acl/Simple'], function(UiAclSimple) {
		new UiAclSimple();
	});
</script>
