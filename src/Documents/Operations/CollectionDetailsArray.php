<?php

namespace RavenDB\Documents\Operations;

use RavenDB\Type\TypedArray;

class CollectionDetailsArray extends TypedArray
{
    public function __construct()
    {
        parent::__construct(CollectionDetails::class);
    }
}
