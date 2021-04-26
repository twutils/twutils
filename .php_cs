<?php

require __DIR__.'/vendor/autoload.php';
require __DIR__.'/bootstrap/app.php';

return (new MattAllan\LaravelCodeStyle\Config())
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(app_path())
            ->in(config_path())
            ->in(database_path('factories'))
            ->in(database_path('seeders'))
            ->in(resource_path('lang'))
            ->in(base_path('routes'))
            ->in(base_path('tests'))
    )
    ->setRiskyAllowed(true)
    ->setRules([
        '@Laravel' => true,
        '@Laravel:risky' => true,
        'ordered_imports' => [
            'sort_algorithm' => 'length',
        ],
        'php_unit_strict' => false,
        'class_attributes_separation' => [
            'elements' => [
                'const',
                'method',
                'property',
            ],
        ],
        'no_spaces_around_offset' => [
            'positions' => [
                'inside',
                'outside',
            ],
        ],
        'cast_spaces' => ['space' => 'single'],
        'no_whitespace_in_blank_line' => true,	
        'binary_operator_spaces' => ['align_double_arrow' => true],
        'no_trailing_comma_in_singleline_array' => true,
        'no_whitespace_before_comma_in_array' => true,
        'whitespace_after_comma_in_array' => true,
        'trim_array_spaces' => true,
        'single_quote' => true,
        'no_extra_blank_lines' => [	
        'break', 'case', 'continue', 'curly_brace_block', 'default', 'extra',	
        'parenthesis_brace_block', 'square_brace_block', 'throw',	
        'use', 'useTrait', 'use_trait'	
        ],
    ])
    ->setCacheFile(__DIR__.'/storage/framework/cache/.php_cs.cache');
