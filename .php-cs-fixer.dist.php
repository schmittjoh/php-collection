<?php
# https://github.com/FriendsOfPHP/PHP-CS-Fixer/blob/3.0/doc/rules/index.rst

$finder = PhpCsFixer\Finder::create()
    ->in(['src', 'tests'])
    ->exclude(['vendor'])
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        '@PHP81Migration' => true,
        'blank_line_after_namespace' => true,
        'blank_line_after_opening_tag' => true,
        'blank_line_before_statement' => true,
        'constant_case' => true,
        'increment_style' => false,
        'lowercase_keywords' => true,
        'normalize_index_brace' => true,
        'no_closing_tag' => true,
        'no_extra_blank_lines' => true,
        'no_singleline_whitespace_before_semicolons' => true,
        'no_spaces_after_function_name' => true,
        'no_spaces_around_offset' => true,
        'no_superfluous_phpdoc_tags' => false,
        'no_unused_imports' => true,
        'no_useless_else' => true,
        'no_whitespace_before_comma_in_array' => true,
        'no_whitespace_in_blank_line' => true,
        'ordered_class_elements' => true,
        'ordered_imports' => true,
        'phpdoc_add_missing_param_annotation' => true,
        'phpdoc_align' => false,
        'phpdoc_order' => true,
        'phpdoc_separation' => false,
        'phpdoc_summary' => false,
        'phpdoc_to_comment' => false,
        'protected_to_private' => true,
        'single_blank_line_at_eof' => true,
        'single_blank_line_before_namespace' => true,
        'single_line_comment_style' => false,
        'single_line_throw' => false,
        'single_quote' => false,
        'single_trait_insert_per_statement' => true,
        'whitespace_after_comma_in_array' => true,
    ])
    ->setFinder($finder);
