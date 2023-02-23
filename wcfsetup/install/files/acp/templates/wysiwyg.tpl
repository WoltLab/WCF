{if !$wysiwygSelector|isset}{assign var=wysiwygSelector value='text'}{/if}

{event name='wysiwyg'}

<script data-relocate="true">
	require(["WoltLabSuite/Core/Component/Ckeditor"], ({ setupCkeditor }) => {
		{jsphrase name='wcf.editor.button.group.block'}
		{jsphrase name='wcf.editor.button.group.format'}
		{jsphrase name='wcf.editor.button.group.list'}
		{jsphrase name='wcf.editor.restoreDraft'}

		const element = document.getElementById('{$wysiwygSelector|encodeJS}');
		if (element === null) {
			throw new Error("Unable to find the source element '{$wysiwygSelector|encodeJS}' for the editor.")
		}

		const features = {
			alignment: true,
			attachment: element.dataset.disableAttachments !== "true",
			autosave: element.dataset.autosave || "",
			code: true,
			codeBlock: true,
			heading: true,
			html: {if $__wcf->getBBCodeHandler()->isAvailableBBCode('html')}true{else}false{/if},
			image: {if $__wcf->getBBCodeHandler()->isAvailableBBCode('img')}true{else}false{/if},
			link: {if $__wcf->getBBCodeHandler()->isAvailableBBCode('url')}true{else}false{/if},
			list: true,
			media: {if $__wcf->session->getPermission('admin.content.cms.canUseMedia')}true{else}false{/if},
			mention: element.dataset.supportMention === "true",
			quoteBlock: true,
			strikethrough: true,
			subscript: true,
			superscript: true,
			spoiler: {if $__wcf->getBBCodeHandler()->isAvailableBBCode('spoiler')}true{else}false{/if},
			table: true,
			underline: true,
		};

		const bbcodes = [
			{foreach from=$__wcf->getBBCodeHandler()->getButtonBBCodes(true) item=__bbcode}
				{
					icon: '{@$__bbcode->wysiwygIcon|encodeJS}',
					name: '{@$__bbcode->bbcodeTag|encodeJS}',
					label: '{@$__bbcode->getButtonLabel()|encodeJS}',
				},
			{/foreach}
		];
		if (features.media) {
			bbcodes.push({
				icon: "file-circle-plus;false",
				name: "media",
				label: '{jslang}wcf.editor.button.media{/jslang}',
			});
		}

		void setupCkeditor(element, features, bbcodes);
	});
</script>
