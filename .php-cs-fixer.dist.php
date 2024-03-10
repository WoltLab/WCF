<?php
$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__.'/wcfsetup/')
    ->notPath('lib/system/api')
    ->notPath('test.php')
    ->notPath('install.php')
    ->notPath('core.functions.php');

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR1' => true,
        '@PSR2' => true,

        'array_push' => true,
        'backtick_to_shell_exec' => true,
        'no_alias_language_construct_call' => true,
        'no_mixed_echo_print' => true,
        'pow_to_exponentiation' => true,
        'random_api_migration' => true,

        'array_syntax' => ['syntax' => 'short'],
        'no_multiline_whitespace_around_double_arrow' => true,
        'no_trailing_comma_in_singleline_array' => true,
        'no_whitespace_before_comma_in_array' => true,
        'normalize_index_brace' => true,
        'whitespace_after_comma_in_array' => true,

        'non_printable_character' => ['use_escape_sequences_in_strings' => true],

        'lowercase_static_reference' => true,
        'magic_constant_casing' => true,
        'magic_method_casing' => true,
        'native_function_casing' => true,
        'native_function_type_declaration_casing' => true,

        'cast_spaces' => ['space' => 'none'],
        'lowercase_cast' => true,
        'no_unset_cast' => true,
        'short_scalar_cast' => true,

        'class_attributes_separation' => true,
        'no_blank_lines_after_class_opening' => true,
        'no_null_property_initialization' => true,
        'self_accessor' => true,
        'single_class_element_per_statement' => true,
        'single_trait_insert_per_statement' => true,

        'no_empty_comment' => true,
        'single_line_comment_style' => ['comment_types' => ['hash']],

        'native_constant_invocation' => ['strict' => false],

        'no_alternative_syntax' => true,
        'no_trailing_comma_in_list_call' => true,
        'no_unneeded_control_parentheses' => ['statements' => ['break', 'clone', 'continue', 'echo_print', 'return', 'switch_case', 'yield', 'yield_from']],
        'no_unneeded_curly_braces' => ['namespaces' => true],
        'switch_continue_to_break' => true,
        'trailing_comma_in_multiline' => ['elements' => ['arrays']],

        'function_typehint_space' => true,
        'lambda_not_used_import' => true,
        'native_function_invocation' => ['include' => ['@internal']],
        'no_unreachable_default_argument_value' => true,
        'nullable_type_declaration_for_default_null_value' => true,
        'return_type_declaration' => true,
        'static_lambda' => true,

        'fully_qualified_strict_types' => ['leading_backslash_in_global_namespace' => true],
        'no_leading_import_slash' => true,
        'no_unused_imports' => true,
        'ordered_imports' => true,

        'declare_equal_normalize' => true,
        'dir_constant' => true,
        'explicit_indirect_variable' => true,
        'function_to_constant' => true,
        'is_null' => true,
        'no_unset_on_property' => true,

        'list_syntax' => ['syntax' => 'short'],

        'clean_namespace' => true,
        'no_leading_namespace_whitespace' => true,
        'single_blank_line_before_namespace' => true,

        'no_homoglyph_names' => true,

        'binary_operator_spaces' => true,
        'concat_space' => ['spacing' => 'one'],
        'increment_style' => ['style' => 'post'],
        'logical_operators' => true,
        'object_operator_without_whitespace' => true,
        'operator_linebreak' => true,
        'standardize_increment' => true,
        'standardize_not_equals' => true,
        'ternary_operator_spaces' => true,
        'ternary_to_elvis_operator' => true,
        'ternary_to_null_coalescing' => true,
        'unary_operator_spaces' => true,

        'no_useless_return' => true,
        'return_assignment' => true,

        'multiline_whitespace_before_semicolons' => true,
        'no_empty_statement' => true,
        'no_singleline_whitespace_before_semicolons' => true,
        'space_after_semicolon' => ['remove_in_empty_for_expressions' => true],

        'escape_implicit_backslashes' => true,
        'explicit_string_variable' => true,
        'heredoc_to_nowdoc' => true,
        'no_binary_string' => true,
        'simple_to_complex_string_variable' => true,

        'array_indentation' => true,
        'blank_line_before_statement' => ['statements' => ['return', 'exit']],
        'compact_nullable_typehint' => true,
        'method_chaining_indentation' => true,
        'no_extra_blank_lines' => ['tokens' => ['case', 'continue', 'curly_brace_block', 'default', 'extra', 'parenthesis_brace_block', 'square_brace_block', 'switch', 'throw', 'use']],
        'no_spaces_around_offset' => true,
    ])
    ->setFinder($finder);
