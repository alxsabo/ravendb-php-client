<?php

namespace RavenDB\Documents\Queries\Facets;

use Symfony\Component\Serializer\Annotation\SerializedName;

class Facet extends FacetBase
{
    /** @SerializedName("FieldName") */
    private string $fieldName;

    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    public function setFieldName(string $fieldName): void
    {
        $this->fieldName = $fieldName;
    }

    public function toFacetToken(Function<Object, String> $addQueryParameter): FacetToken
    {
        return FacetToken::create($this, $addQueryParameter);
    }
}
