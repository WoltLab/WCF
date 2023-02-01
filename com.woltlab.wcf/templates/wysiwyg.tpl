{event name='beforeEditorJavaScript'}

<script data-relocate="true">
	require(["WoltLabSuite/Core/Component/Ckeditor", "/wcf/editor/dist/bundle.js"], ({ setupCkeditor }) => {
		{jsphrase name='wcf.editor.restoreDraft'}

		const element = document.getElementById('{if $wysiwygSelector|isset}{$wysiwygSelector|encodeJS}{else}text{/if}');

		// TODO: This is awful, it’s barely readable and cumbersome
		// because there is no auto-complete or validation from the IDE.
		const toolbar = [
			"heading",

			"|",

			"bold",
			"italic",
			{
				label: "woltlabToolbarGroup_format",
				items: [
					"underline",
					"strikethrough",
					"subscript",
					"superscript",
					"code",
				],
			},

			"|",

			{
				label: "woltlabToolbarGroup_list",
				items: [
					"bulletedList",
					"numberedList",
				],
			},

			"alignment",
			{if $__wcf->getBBCodeHandler()->isAvailableBBCode('url')}
				"link",
			{/if}
			{if $__wcf->getBBCodeHandler()->isAvailableBBCode('img')}
				"insertImage",
			{/if}

			{
				label: "TODO: Insert block",
				icon: "plus",
				items: [
					"insertTable",
					"blockQuote",
					"codeBlock",
					{if $__wcf->getBBCodeHandler()->isAvailableBBCode('spoiler')}
						"spoiler",
					{/if}
					{if $__wcf->getBBCodeHandler()->isAvailableBBCode('html')}
						"htmlEmbed",
					{/if}
					{if $__wcf->session->getPermission('admin.content.cms.canUseMedia')}
						"woltlabBbcode_media",
					{/if}
				],
			},
		];

		const woltlabToolbarGroup = {
			"format": {
				icon: "ellipsis;false",
				label: "TODO: Format text",
			},
			"list": {
				icon: "list;false",
				label: "TODO: Insert list",
			},
		};

		let woltlabBbcode = [
			// TODO: This implicitly causes the button to be present twice, because
			// 		 the bbcode plugin does not check if the button already exists.
			{if $__wcf->session->getPermission('admin.content.cms.canUseMedia')}
				{
					icon: "file-circle-plus;false",
					name: "media",
					label: "TODO: woltlab media bbcode"
				},
			{/if}

			{foreach from=$__wcf->getBBCodeHandler()->getButtonBBCodes(true) item=__bbcode}
				{
					icon: '{@$__bbcode->wysiwygIcon|encodeJS}',
					name: '{@$__bbcode->bbcodeTag|encodeJS}',
					label: '{@$__bbcode->getButtonLabel()|encodeJS}',
				},
			{/foreach}
		];

		// TODO: This removes already exisitng functionalities and perhaps
		// should be handled on the server?
		woltlabBbcode = woltlabBbcode.filter(({ name }) => {
			return name !== "html"
				&& name !== "tt"
				&& name !== "code"
				&& name !== "spoiler";
		});

		woltlabBbcode.forEach(({ name }) => {
			toolbar.push(`woltlabBbcode_${ name }`);
		});

		void setupCkeditor(element, {
			toolbar,
			woltlabBbcode,
			woltlabToolbarGroup,
		}, {
			attachment: element.dataset.disableAttachments !== "true",
			autosave: element.dataset.autosave || "",
			media: {if $__wcf->session->getPermission('admin.content.cms.canUseMedia')}true{else}false{/if},
			mention: element.dataset.supportMention === "true",
		}).then((ckeditor) => {
			{if $__wcf->session->getPermission('admin.content.cms.canUseMedia')}
				require(["WoltLabSuite/Core/Media/Manager/Editor"], ({ MediaManagerEditor }) => {
					new MediaManagerEditor({
						ckeditor,
					});
				});
			{/if}
		});
	});
</script>
