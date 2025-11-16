<?php

$finder = PhpCsFixer\Finder::create()
    ->name('*.php')
    ->exclude('vendor');

$config = new PhpCsFixer\Config();

return $config->setRules([
        '@PSR2' => true,
        'single_quote' => true,
        'array_syntax' => ['syntax' => 'short'],
        'binary_operator_spaces' => [
            'default' => 'single_space',
        ],
        'braces' => [
            'allow_single_line_closure' => true,
        ],
        'concat_space' => [
            'spacing' => 'one',
        ],
        'declare_equal_normalize' => true,
        'function_typehint_space' => true,
        'new_with_braces' => true,
        'no_empty_statement' => true,
        'no_leading_import_slash' => true,
        'no_multiline_whitespace_around_double_arrow' => true,
        'no_short_bool_cast' => true,
        'no_singleline_whitespace_before_semicolons' => true,
        'no_unused_imports' => true,
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'no_whitespace_before_comma_in_array' => true,
        'no_whitespace_in_blank_line' => true,
        'normalize_index_brace' => true,
        'object_operator_without_whitespace' => true,
        'phpdoc_indent' => true,
        'phpdoc_no_access' => true,
        'phpdoc_no_package' => true,
        'phpdoc_scalar' => true,
        'phpdoc_trim' => true,
        'phpdoc_types' => true,
        'phpdoc_var_without_name' => true,
        'single_trait_insert_per_statement' => true,
        'trailing_comma_in_multiline' => [
            'elements' => ['arrays'],
        ],
        'trim_array_spaces' => true,
        'unary_operator_spaces' => true,
        'whitespace_after_comma_in_array' => true,
    ])
    ->setFinder($finder);
