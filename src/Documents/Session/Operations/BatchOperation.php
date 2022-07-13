<?php

namespace RavenDB\Documents\Session\Operations;

use RavenDB\Constants\DocumentsMetadata;
use RavenDB\Documents\Commands\Batches\ClusterWideBatchCommand;
use RavenDB\Documents\Commands\Batches\CommandType;
use RavenDB\Documents\Commands\Batches\SingleNodeBatchCommand;
use RavenDB\Documents\Operations\PatchStatus;
use RavenDB\Documents\Session\ActionsToRunOnSuccess;
use RavenDB\Documents\Session\AfterSaveChangesEventArgs;
use RavenDB\Documents\Session\DocumentInfo;
use RavenDB\Documents\Session\DocumentInfoArray;
use RavenDB\Documents\Session\InMemoryDocumentSessionOperations;
use RavenDB\Exceptions\ClientVersionMismatchException;
use RavenDB\Exceptions\IllegalArgumentException;
use RavenDB\Exceptions\IllegalStateException;
use RavenDB\Json\BatchCommandResult;

// !status: IN PROGRESS
class BatchOperation
{
    private InMemoryDocumentSessionOperations $session;

    private array $entities;
    private int $sessionCommandsCount = 0;
    private int $allCommandsCount = 0;
    private ActionsToRunOnSuccess $onSuccessfulRequest;

    private ?DocumentInfoArray $modifications = null;

    public function __construct(InMemoryDocumentSessionOperations $session)
    {
        $this->session = $session;
    }

    /**
     * @throws IllegalArgumentException|IllegalStateException
     */
    public function createRequest(): ?SingleNodeBatchCommand
    {
        $result = $this->session->prepareForSaveChanges();
        $this->onSuccessfulRequest = $result->getOnSuccess();
        $this->sessionCommandsCount = count($result->getSessionCommands());

        foreach ($result->getDeferredCommands() as $deferredCommand) {
            $result->addSessionCommand($deferredCommand);
        }

        $this->session->validateClusterTransaction($result);

        $this->allCommandsCount = count($result->getSessionCommands());

        if ($this->allCommandsCount == 0) {
            return null;
        }

        $this->session->incrementRequestCount();

        $this->entities = $result->getEntities();

        if ($this->session->getTransactionMode()->isClusterWide()) {
            return new ClusterWideBatchCommand(
                $this->session->getConventions(),
                $result->getSessionCommands(),
                $result->getOptions(),
                $this->session->disableAtomicDocumentWritesInClusterWideTransaction
            );
        }

        return new SingleNodeBatchCommand(
            $this->session->getConventions(),
            $result->getSessionCommands(),
            $result->getOptions()
        );
    }

    public function setResult(BatchCommandResult $result): void
    {
        $getCommandType = function ($batchResult): CommandType {
            $type = null;
            if (key_exists('Type', $batchResult)) {
                $type = $batchResult['Type'];
            }

            if ($type == null) {
                return CommandType::none();
            }

            return CommandType::parseCSharpValue($type);
        };

        if (empty($result->getResults())) {
            $this->throwOnNullResults();
            return;
        }

        $this->onSuccessfulRequest->clearSessionStateAfterSuccessfulSaveChanges();

        if ($this->session->getTransactionMode()->isClusterWide()) {
            if ($result->getTransactionIndex() <= 0) {
                throw new ClientVersionMismatchException(
                    "Cluster transaction was send to a node that is not supporting it. " .
                    "So it was executed ONLY on the requested node on " . $this->session->getRequestExecutor()->getUrl());
            }
        }

        for ($i = 0; $i < $this->sessionCommandsCount; $i++) {
            if ($i >= count($result->getResults())) {
                continue;
            }
            $batchResult = $result->getResults()[$i];
            if ($batchResult == null) {
                continue;
            }

            $type = $getCommandType($batchResult);

            switch ($type->getValue()) {
                case CommandType::PUT:
                    $this->handlePut($i, $batchResult, false);
                    break;
                case CommandType::FORCE_REVISION_CREATION:
//                    handleForceRevisionCreation(batchResult);
                    break;
                case CommandType::DELETE:
                    $this->handleDelete($batchResult);
                    break;
                case CommandType::COMPARE_EXCHANGE_PUT:
                    $this->handleCompareExchangePut($batchResult);
                    break;
                case CommandType::COMPARE_EXCHANGE_DELETE:
                    $this->handleCompareExchangeDelete($batchResult);
                    break;
                default:
                    throw new IllegalStateException("Command " . $type . " is not supported");
            }
        }

        for ($i = $this->sessionCommandsCount; $i < $this->allCommandsCount; $i++) {
            if ($i >= count($result->getResults())) {
                continue;
            }
            $batchResult = $result->getResults()[$i];
            if ($batchResult == null) {
                continue;
            }

            $type = $getCommandType($batchResult);

            switch ($type->getValue()) {
                case CommandType::PUT:
                    $this->handlePut($i, $batchResult, true);
                    break;
                case CommandType::DELETE:
                    $this->handleDelete($batchResult);
                    break;
                case CommandType::PATCH:
                    $this->handlePatch($batchResult);
                    break;
                case CommandType::ATTACHMENT_PUT:
//                    handleAttachmentPut(batchResult);
                    break;
                case CommandType::ATTACHMENT_DELETE:
//                    handleAttachmentDelete(batchResult);
                    break;
                case CommandType::ATTACHMENT_MOVE:
//                    handleAttachmentMove(batchResult);
                    break;
                case CommandType::ATTACHMENT_COPY:
//                    handleAttachmentCopy(batchResult);
                    break;
                case CommandType::COMPARE_EXCHANGE_PUT:
                case CommandType::COMPARE_EXCHANGE_DELETE:
                case CommandType::FORCE_REVISION_CREATION:
                    break;
                case CommandType::COUNTERS:
//                    handleCounters(batchResult);
                    break;
                case CommandType::TIME_SERIES:
//                    //TODO: RavenDB-13474 add to time series cache
                    break;
                case CommandType::TIME_SERIES_COPY:
                    break;
                case CommandType::BATCH_PATCH:
                    break;
                default:
                    throw new IllegalStateException("Command " . $type . " is not supported");
            }
        }
        $this->finalizeResult();
    }

    private function finalizeResult(): void
    {
        if (($this->modifications == null) || !count($this->modifications)) {
            return;
        }

        /**
         * @var string $id
         * @var DocumentInfo $documentInfo
         */
        foreach ($this->modifications as $id => $documentInfo) {
            $this->applyMetadataModifications($id, $documentInfo);
        }
    }

    private function applyMetadataModifications(string $id, DocumentInfo $documentInfo): void
    {
        $documentInfo->setMetadataInstance(null);
        $metadata = $documentInfo->getMetadata();
        $cloned = $metadata; // cloned @todo: check is this realy cloned object and we don't have to do nothing here?

        $cloned[DocumentsMetadata::CHANGE_VECTOR] = $metadata[DocumentsMetadata::CHANGE_VECTOR] ?? $documentInfo->getChangeVector();
        $documentInfo->setMetadata($cloned);

        $document = $documentInfo->getDocument();
        $documentCopy = $document; // cloned @todo: check is this realy cloned object and we don't have to do nothing here?
        $documentCopy[DocumentsMetadata::KEY] = $documentInfo->getMetadata();

        $documentInfo->setDocument($documentCopy);
    }

    private function &getOrAddModifications(
        string       $id,
        DocumentInfo &$documentInfo,
        bool         $applyModifications
    ): DocumentInfo
    {
        if ($this->modifications == null) {
            $this->modifications = new DocumentInfoArray();
        }

        $modifiedDocumentInfo = $this->modifications->getValue($id);
        if ($modifiedDocumentInfo != null) {
            if ($applyModifications) {
                $this->applyMetadataModifications($id, $modifiedDocumentInfo);
            }
        } else {
            $this->modifications->offsetSet($id, $modifiedDocumentInfo = $documentInfo);
        }

        return $modifiedDocumentInfo;
    }

    private function handleCompareExchangePut($batchResult): void
    {
        $this->handleCompareExchangeInternal(CommandType::compareExchangePut(), $batchResult);
    }

    private function handleCompareExchangeDelete($batchResult): void
    {
        $this->handleCompareExchangeInternal(CommandType::compareExchangeDelete(), $batchResult);
    }

    private function handleCompareExchangeInternal(CommandType $commandType, $batchResult): void
    {
        if (!array_key_exists('Key', $batchResult) || empty($batchResult['Key'])) {
            $this->throwMissingField($commandType, 'Key');
        }
        $key = strval($batchResult['Key']);

        if (!array_key_exists('Index', $batchResult) || empty($batchResult['Index'])) {
            $this->throwMissingField($commandType, 'Index');
        }
        $index = intval($batchResult['Index']);

        $clusterSession = $this->session->getClusterSession();
        $clusterSession->updateState($key, $index);
    }

//    private void handleAttachmentCopy(ObjectNode batchResult) {
//        handleAttachmentPutInternal(batchResult, CommandType.ATTACHMENT_COPY, "Id", "Name", "DocumentChangeVector");
//    }
//
//    private void handleAttachmentMove(ObjectNode batchResult) {
//        handleAttachmentDeleteInternal(batchResult, CommandType.ATTACHMENT_MOVE, "Id", "Name", "DocumentChangeVector");
//        handleAttachmentPutInternal(batchResult, CommandType.ATTACHMENT_MOVE, "DestinationId", "DestinationName", "DocumentChangeVector");
//    }
//
//    private void handleAttachmentDelete(ObjectNode batchResult) {
//        handleAttachmentDeleteInternal(batchResult, CommandType.ATTACHMENT_DELETE, Constants.Documents.Metadata.ID, "Name", "DocumentChangeVector");
//    }
//
//    private void handleAttachmentDeleteInternal(ObjectNode batchResult, CommandType type, String idFieldName, String attachmentNameFieldName, String documentChangeVectorFieldName) {
//        String id = getStringField(batchResult, type, idFieldName);
//
//        DocumentInfo sessionDocumentInfo = _session.documentsById.getValue(id);
//        if (sessionDocumentInfo == null) {
//            return;
//        }
//
//        DocumentInfo documentInfo = getOrAddModifications(id, sessionDocumentInfo, true);
//
//        String documentChangeVector = getStringField(batchResult, type, documentChangeVectorFieldName, false);
//        if (documentChangeVector != null) {
//            documentInfo.setChangeVector(documentChangeVector);
//        }
//
//        JsonNode attachmentsJson = documentInfo.getMetadata().get(Constants.Documents.Metadata.ATTACHMENTS);
//        if (attachmentsJson == null || attachmentsJson.isNull() || attachmentsJson.size() == 0) {
//            return;
//        }
//
//        String name = getStringField(batchResult, type, attachmentNameFieldName);
//
//        ArrayNode attachments = JsonExtensions.getDefaultMapper().createArrayNode();
//        documentInfo.getMetadata().set(Constants.Documents.Metadata.ATTACHMENTS, attachments);
//
//        for (int i = 0; i < attachmentsJson.size(); i++) {
//            ObjectNode attachment = (ObjectNode) attachmentsJson.get(i);
//            String attachmentName = getStringField(attachment, type, "Name");
//            if (attachmentName.equals(name)) {
//                continue;
//            }
//
//            attachments.add(attachment);
//        }
//    }
//
//    private void handleAttachmentPut(ObjectNode batchResult) {
//        handleAttachmentPutInternal(batchResult, CommandType.ATTACHMENT_PUT, "Id", "Name", "DocumentChangeVector");
//    }
//
//    private void handleAttachmentPutInternal(ObjectNode batchResult, CommandType type, String idFieldName, String attachmentNameFieldName, String documentChangeVectorFieldName) {
//        String id = getStringField(batchResult, type, idFieldName);
//
//        DocumentInfo sessionDocumentInfo = _session.documentsById.getValue(id);
//        if (sessionDocumentInfo == null) {
//            return;
//        }
//
//        DocumentInfo documentInfo = getOrAddModifications(id, sessionDocumentInfo, false);
//
//        String documentChangeVector = getStringField(batchResult, type, documentChangeVectorFieldName, false);
//        if (documentChangeVector != null) {
//            documentInfo.setChangeVector(documentChangeVector);
//        }
//
//        ObjectMapper mapper = JsonExtensions.getDefaultMapper();
//        ArrayNode attachments = (ArrayNode) documentInfo.getMetadata().get(Constants.Documents.Metadata.ATTACHMENTS);
//        if (attachments == null) {
//            attachments = mapper.createArrayNode();
//            documentInfo.getMetadata().set(Constants.Documents.Metadata.ATTACHMENTS, attachments);
//        }
//
//        ObjectNode dynamicNode = mapper.createObjectNode();
//        attachments.add(dynamicNode);
//        dynamicNode.put("ChangeVector", getStringField(batchResult, type, "ChangeVector"));
//        dynamicNode.put("ContentType", getStringField(batchResult, type, "ContentType"));
//        dynamicNode.put("Hash", getStringField(batchResult, type, "Hash"));
//        dynamicNode.put("Name", getStringField(batchResult, type, "Name"));
//        dynamicNode.put("Size", getLongField(batchResult, type, "Size"));
//    }

    private function handlePatch(array $batchResult): void
    {
        $patchStatus = null;
        if (array_key_exists('PatchStatus', $batchResult)) {
            $patchStatus = $batchResult['PatchStatus'];
        }
        if (($patchStatus == null) || empty($patchStatus)) {
            self::throwMissingField(CommandType::patch(), 'PatchStatus');
        }

        $status = new PatchStatus($patchStatus);

        switch ($status->getValue()) {
            case PatchStatus::CREATED:
            case PatchStatus::PATCHED:
                if (!array_key_exists('ModifiedDocument', $batchResult)) {
                    return;
                }
                $document = $batchResult['ModifiedDocument'];

                $id = $this->getStringField($batchResult, CommandType::put(), "Id");

                $sessionDocumentInfo = $this->session->documentsById->getValue($id);
                if ($sessionDocumentInfo == null) {
                    return;
                }

                $documentInfo = $this->getOrAddModifications($id, $sessionDocumentInfo, true);

                $changeVector = $this->getStringField($batchResult, CommandType::patch(), "ChangeVector");
                $lastModified = $this->getStringField($batchResult, CommandType::patch(), "LastModified");

                $documentInfo->setChangeVector($changeVector);

                $metadata = $documentInfo->getMetadata();
                $metadata[DocumentsMetadata::ID] = $id;
                $metadata[DocumentsMetadata::CHANGE_VECTOR] = $changeVector;
                $metadata[DocumentsMetadata::LAST_MODIFIED] = $lastModified;
                $documentInfo->setMetadata($metadata);

                $documentInfo->setDocument($document);
                $this->applyMetadataModifications($id, $documentInfo);

                if ($documentInfo->getEntity() != null) {
                    $entity = $documentInfo->getEntity();
                    $this->session->getEntityToJson()->populateEntity($entity, $id, $documentInfo->getDocument());
                    $afterSaveChangesEventArgs = new AfterSaveChangesEventArgs($this->session, $documentInfo->getId(), $documentInfo->getEntity());
                    $this->session->onAfterSaveChangesInvoke($afterSaveChangesEventArgs);
                }

                break;
        }
    }

    private function handleDelete(array $batchResult): void
    {
        $this->handleDeleteInternal($batchResult, CommandType::delete());
    }

    private function handleDeleteInternal(array $batchResult, CommandType $type): void
    {
        $id = $this->getStringField($batchResult, $type, "Id");

        /** @var DocumentInfo $documentInfo */
        $documentInfo = $this->session->documentsById->getValue($id);
        if ($documentInfo == null) {
            return;
        }

        $this->session->documentsById->remove($id);

        if ($documentInfo->getEntity() != null) {
            $this->session->documentsByEntity->remove($documentInfo->getEntity());
            $this->session->deletedEntities->remove($documentInfo->getEntity());
        }
    }

//    private void handleForceRevisionCreation(ObjectNode batchResult) {
//        // When forcing a revision for a document that does Not have any revisions yet then the HasRevisions flag is added to the document.
//        // In this case we need to update the tracked entities in the session with the document new change-vector.
//
//        if (!getBooleanField(batchResult, CommandType.FORCE_REVISION_CREATION, "RevisionCreated")) {
//            // no forced revision was created...nothing to update.
//            return;
//        }
//
//        String id = getStringField(batchResult, CommandType.FORCE_REVISION_CREATION, Constants.Documents.Metadata.ID);
//        String changeVector = getStringField(batchResult, CommandType.FORCE_REVISION_CREATION, Constants.Documents.Metadata.CHANGE_VECTOR);
//
//        DocumentInfo documentInfo = _session.documentsById.getValue(id);
//        if (documentInfo == null) {
//            return;
//        }
//
//        documentInfo.setChangeVector(changeVector);
//
//        handleMetadataModifications(documentInfo, batchResult, id, changeVector);
//
//        AfterSaveChangesEventArgs afterSaveChangesEventArgs = new AfterSaveChangesEventArgs(_session, documentInfo.getId(), documentInfo.getEntity());
//        _session.onAfterSaveChangesInvoke(afterSaveChangesEventArgs);
//    }
//
    private function handlePut(int $index, array $batchResult, bool $isDeferred): void
    {
        $entity = null;
        $documentInfo = null;

        if (!$isDeferred) {
            $entity = $this->entities[$index];

            $documentInfo = $this->session->documentsByEntity->get($entity);

            if ($documentInfo == null) {
                return;
            }
        }

        $id = $this->getStringField($batchResult, CommandType::put(), DocumentsMetadata::ID);
        $changeVector = $this->getStringField($batchResult, CommandType::put(), DocumentsMetadata::CHANGE_VECTOR);

        if ($isDeferred) {
            $sessionDocumentInfo = $this->session->documentsById->getValue($id);
            if ($sessionDocumentInfo == null) {
                return;
            }

            $documentInfo = $this->getOrAddModifications($id, $sessionDocumentInfo, true);
            $entity = $documentInfo->getEntity();
        }

        $this->handleMetadataModifications($documentInfo, $batchResult, $id, $changeVector);

        $this->session->documentsById->add($documentInfo);

        if ($entity != null) {
            $this->session->getGenerateEntityIdOnTheClient()->trySetIdentity($entity, $id);
        }

        $afterSaveChangesEventArgs = new AfterSaveChangesEventArgs($this->session, $documentInfo->getId(), $documentInfo->getEntity());
        $this->session->onAfterSaveChangesInvoke($afterSaveChangesEventArgs);
    }

    private function handleMetadataModifications(
        DocumentInfo $documentInfo,
        array        $batchResult,
        string       $id,
        string       $changeVector
    ): void
    {
        foreach ($batchResult as $key => $value) {
            if ($key == "Type") continue;

            $metadata = $documentInfo->getMetadata();
            $metadata[$key] = $value;
            $documentInfo->setMetadata($metadata);
        }
        $documentInfo->setId($id);
        $documentInfo->setChangeVector($changeVector);

        $this->applyMetadataModifications($id, $documentInfo);
    }

//    private void handleCounters(ObjectNode batchResult) {
//
//        String docId = getStringField(batchResult, CommandType.COUNTERS, "Id");
//
//        ObjectNode countersDetail = (ObjectNode) batchResult.get("CountersDetail");
//        if (countersDetail == null) {
//            throwMissingField(CommandType.COUNTERS, "CountersDetail");
//        }
//
//        ArrayNode counters = (ArrayNode) countersDetail.get("Counters");
//        if (counters == null) {
//            throwMissingField(CommandType.COUNTERS, "Counters");
//        }
//
//        Tuple<Boolean, Map<String, Long>> cache = _session.getCountersByDocId().get(docId);
//        if (cache == null) {
//            cache = Tuple.create(false, new TreeMap<>(String::compareToIgnoreCase));
//            _session.getCountersByDocId().put(docId, cache);
//        }
//
//        String changeVector = getStringField(batchResult, CommandType.COUNTERS, "DocumentChangeVector", false);
//        if (changeVector != null) {
//            DocumentInfo documentInfo = _session.documentsById.getValue(docId);
//            if (documentInfo != null) {
//                documentInfo.setChangeVector(changeVector);
//            }
//        }
//
//        for (JsonNode counter : counters) {
//            JsonNode name = counter.get("CounterName");
//            JsonNode value = counter.get("TotalValue");
//
//            if (name != null && !name.isNull() && value != null && !value.isNull()) {
//                cache.second.put(name.asText(), value.longValue());
//            }
//        }
//    }

    private function getStringField(
        array       $json,
        CommandType $type,
        string      $fieldName,
        bool        $throwOnMissing = true
    ): ?string
    {
        $jsonNode = null;
        if (key_exists($fieldName, $json)) {
            $jsonNode = $json[$fieldName];
        }

        if (($jsonNode == null) && $throwOnMissing) {
            self::throwMissingField($type, $fieldName);
        }

        return (string)$jsonNode;
    }

//    private static Long getLongField(ObjectNode json, CommandType type, String fieldName) {
//        JsonNode jsonNode = json.get(fieldName);
//        if (jsonNode == null || !jsonNode.isNumber()) {
//            throwMissingField(type, fieldName);
//        }
//
//        return jsonNode.asLong();
//    }
//
//    private static boolean getBooleanField(ObjectNode json, CommandType type, String fieldName) {
//        JsonNode jsonNode = json.get(fieldName);
//        if (jsonNode == null || !jsonNode.isBoolean()) {
//            throwMissingField(type, fieldName);
//        }
//
//        return jsonNode.asBoolean();
//    }


    private static function throwMissingField(CommandType $type, string $fieldName): void
    {
        throw new IllegalStateException($type . " response is invalid. Field '" . $fieldName . "' is missing.");
    }

    private static function throwOnNullResults(): void
    {
        throw new IllegalStateException("Received empty response from the server. This is not supposed to happen and is likely a bug.");
    }
}
