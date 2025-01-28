<?php

namespace RavenDB\Documents\Queries\Facets;

use Closure;
use RavenDB\Documents\Session\Tokens\FacetToken;
use Symfony\Component\Serializer\Annotation\SerializedName;

class Facet extends FacetBase
{
    /** @SerializedName("FieldName") */
    private ?string $fieldName = null;

    public function __construct(?string $fieldName = null)
    {
        parent::__construct();

        $this->fieldName = $fieldName;
    }

    public function getFieldName(): ?string
    {
        return $this->fieldName;
    }

    public function setFieldName(?string $fieldName): void
    {
        $this->fieldName = $fieldName;
    }

    public function toFacetToken(Closure $addQueryParameter): FacetToken
    {
        return FacetToken::create($this, $addQueryParameter);
    }
}
