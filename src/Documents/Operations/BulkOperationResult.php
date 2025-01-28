<?php

namespace RavenDB\Documents\Operations;

use Symfony\Component\Serializer\Annotation\SerializedName;

class BulkOperationResult
{
    #[SerializedName('Total')]
    private ?int $total = null;
    #[SerializedName('DocumentsProcessed')]
    private ?int $documentsProcessed = null;
    #[SerializedName('AttachmentsProcessed')]
    private ?int $attachmentsProcessed = null;
    #[SerializedName('CountersProcessed')]
    private ?int $countersProcessed = null;
    #[SerializedName('TimeSeriesProcessed')]
    private ?int $timeSeriesProcessed = null;
    #[SerializedName('Query')]
    private ?string $query = null;
    // array<BulkOperationDetailsInterface> $details
    #[SerializedName('Details')]
    private ?BulkOperationDetailsArray $details = null;

//    private bool $shouldPersist = false;
//    private bool $canMerge = true;
//    private string $message = "Processed {Total:#,#0} items.";

    public function __construct()
    {
        $this->details = new BulkOperationDetailsArray();
    }

    public function getTotal(): ?int
    {
        return $this->total;
    }

    public function setTotal(?int $total): void
    {
        $this->total = $total;
    }

    public function getDocumentsProcessed(): ?int
    {
        return $this->documentsProcessed;
    }

    public function setDocumentsProcessed(?int $documentsProcessed): void
    {
        $this->documentsProcessed = $documentsProcessed;
    }

    public function getAttachmentsProcessed(): ?int
    {
        return $this->attachmentsProcessed;
    }

    public function setAttachmentsProcessed(?int $attachmentsProcessed): void
    {
        $this->attachmentsProcessed = $attachmentsProcessed;
    }

    public function getCountersProcessed(): ?int
    {
        return $this->countersProcessed;
    }

    public function setCountersProcessed(?int $countersProcessed): void
    {
        $this->countersProcessed = $countersProcessed;
    }

    public function getTimeSeriesProcessed(): ?int
    {
        return $this->timeSeriesProcessed;
    }

    public function setTimeSeriesProcessed(?int $timeSeriesProcessed): void
    {
        $this->timeSeriesProcessed = $timeSeriesProcessed;
    }

    public function getQuery(): ?string
    {
        return $this->query;
    }

    public function setQuery(?string $query): void
    {
        $this->query = $query;
    }

    public function getDetails(): ?BulkOperationDetailsArray
    {
        return $this->details;
    }

    public function setDetails(?BulkOperationDetailsArray $details): void
    {
        $this->details = $details;
    }
}
