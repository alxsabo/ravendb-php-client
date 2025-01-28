<?php

namespace RavenDB\Documents\Queries\Suggestions;

use RavenDB\Type\StringArray;

interface SuggestionBuilderInterface
{
    /**
     * Usage:
     *   - byField("fieldName", "term");
     *   - byField("fieldName", ["term1", "term2"]);
     *
     * @param string|null $fieldName
     * @param string|StringArray|array|null $terms
     * @return SuggestionOperationsInterface
     */
    function byField(?string $fieldName, null|string|StringArray|array $terms): SuggestionOperationsInterface;
}
