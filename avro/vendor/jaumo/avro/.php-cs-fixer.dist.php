<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

return new class() extends Config
{
    public function __construct()
    {
        parent::__construct('avro-php');

        $this->setFinder(
            Finder::create()
                ->exclude('vendor')
                ->in(__DIR__)
        );

        $this->setRiskyAllowed(true);
    }

    public function getRules(): array
    {
        $rules = [
            '@PSR2' => true,
            'array_syntax' => ['syntax' => 'short'],
            'native_function_invocation' => [
                'include' => ['@internal']
            ],
            'blank_line_after_opening_tag' => true,
            'blank_line_after_namespace' => true,
            'blank_line_before_statement' => ['statements' => ['return']],
            'braces' => true,
            'cast_spaces' => true,
            'class_definition' => true,
            'combine_consecutive_unsets' => true,
            'concat_space' => false,
            'declare_strict_types' => true,
            'elseif' => true,
            'encoding' => true,
            'full_opening_tag' => true,
            'function_declaration' => true,
            'function_typehint_space' => true,
            'single_line_comment_style' => ['comment_types' => ['hash']],
            'header_comment' => [
                'comment_type' => 'PHPDoc',
                'header' => '',
                'location' => 'after_open',
                'separate' => 'both',
            ],
            'include' => true,
            'indentation_type' => true,
            'linebreak_after_opening_tag' => true,
            'line_ending' => true,
            'constant_case' => ['case' => 'lower'],
            'lowercase_keywords' => true,
            'method_argument_space' => [
                'on_multiline' => 'ignore'
            ],
            'class_attributes_separation' => ['elements' => ['method' => 'one']],
            'modernize_types_casting' => true,
            'native_function_casing' => true,
            'new_with_braces' => true,
            'no_alias_functions' => true,
            'no_blank_lines_after_class_opening' => true,
            'no_closing_tag' => true,
            'no_empty_statement' => true,
            'no_extra_blank_lines' => true,
            'no_leading_import_slash' => true,
            'no_leading_namespace_whitespace' => true,
            'no_multiline_whitespace_around_double_arrow' => true,
            'multiline_whitespace_before_semicolons' => false,
            'no_short_bool_cast' => true,
            'echo_tag_syntax' => ['format' => 'long'],
            'no_singleline_whitespace_before_semicolons' => true,
            'no_spaces_around_offset' => true,
            'no_trailing_comma_in_list_call' => true,
            'no_trailing_comma_in_singleline_array' => true,
            'no_unneeded_control_parentheses' => true,
            'no_unreachable_default_argument_value' => true,
            'no_unused_imports' => true,
            'no_useless_else' => true,
            'no_useless_return' => true,
            'no_spaces_inside_parenthesis' => true,
            'no_trailing_whitespace_in_comment' => true,
            'no_whitespace_before_comma_in_array' => true,
            'no_whitespace_in_blank_line' => true,
            'normalize_index_brace' => true,
            'not_operator_with_successor_space' => false,
            'object_operator_without_whitespace' => true,
            'ordered_imports' => true,
            'phpdoc_indent' => true,
            'phpdoc_inline_tag_normalizer' => ['tags' => ['inheritdoc']],
            'psr_autoloading' => true,
            'return_type_declaration' => true,
            'semicolon_after_instruction' => true,
            'short_scalar_cast' => true,
            'simplified_null_return' => false,
            'single_blank_line_at_eof' => true,
            'single_class_element_per_statement' => true,
            'single_import_per_statement' => true,
            'single_line_after_imports' => true,
            'single_quote' => true,
            'standardize_not_equals' => true,
            'strict_comparison' => true,
            'strict_param' => true,
            'switch_case_semicolon_to_colon' => true,
            'switch_case_space' => true,
            'ternary_operator_spaces' => true,
            'trailing_comma_in_multiline' => ['elements' => ['arrays']],
            'trim_array_spaces' => true,
            'unary_operator_spaces' => true,
            'visibility_required' => true,
            'whitespace_after_comma_in_array' => true,
        ];

        $rules['header_comment'] = $this->headerComment($rules['header_comment']);

        return $rules;
    }

    private function headerComment(array $rules): array
    {
        if (\file_exists('.docheader')) {
            $header = \file_get_contents('.docheader');
        } else {
            $header = $rules['header'];
        }

        // remove comments from existing .docheader or crash
        $header = \str_replace(['/**', ' */', ' * ', ' *'], '', $header);
        $package = 'unknown';

        if (\file_exists('composer.json')) {
            $package = \json_decode(\file_get_contents('composer.json'))->name;
        }

        $header = \str_replace(['%package%', '%year%'], [$package, (new \DateTime('now'))->format('Y')], $header);

        $rules['header'] = \trim($header);

        return $rules;
    }
};

