require(['WoltLabSuite/Core/Form/Builder/Field/Dependency/IsNotClicked'], function(IsNotClickedFieldDependency) {
	// dependency '{@$dependency->getId()}'
	new IsNotClickedFieldDependency(
		'{@$dependency->getDependentNode()->getPrefixedId()}Container',
		'{@$dependency->getField()->getPrefixedId()}'
	);
});
