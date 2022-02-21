<?php

namespace RavenDB\Documents\Session;

//    @todo implement this class
use RavenDB\Documents\Operations\CompareExchangeSessionValue;
use RavenDB\Exceptions\IllegalStateException;

class ClusterTransactionOperationsBase
{
    protected DocumentSession $session;
    private $state = [];

    public function __construct(DocumentSession $session)
    {
        if (!$session->getTransactionMode()->isClusterWide()) {
            throw new IllegalStateException("This function is part of cluster transaction session, in order to use it you have to open the Session with ClusterWide option.");
        }

        $this->session = $session;
    }

    public function getSession(): DocumentSession
    {
        return $this->session;
    }

    public function getNumberOfTrackedCompareExchangeValues(): int
    {
        return count($this->state);
    }

//    public boolean isTracked(String key) {
//        Reference<CompareExchangeSessionValue> ref = new Reference<>();
//        return tryGetCompareExchangeValueFromSession(key, ref);
//    }
//
//    public <T> CompareExchangeValue<T> createCompareExchangeValue(String key, T item) {
//        if (key == null) {
//            throw new IllegalArgumentException("Key cannot be null");
//        }
//
//        Reference<CompareExchangeSessionValue> sessionValueRef = new Reference<>();
//        if (!tryGetCompareExchangeValueFromSession(key, sessionValueRef)) {
//            sessionValueRef.value = new CompareExchangeSessionValue(key, 0, CompareExchangeValueState.NONE);
//            _state.put(key, sessionValueRef.value);
//        }
//
//        return sessionValueRef.value.create(item);
//    }
//
//    public <T> void deleteCompareExchangeValue(CompareExchangeValue<T> item) {
//        if (item == null) {
//            throw new IllegalArgumentException("Item cannot be null");
//        }
//
//        Reference<CompareExchangeSessionValue> sessionValueRef = new Reference<>();
//        if (!tryGetCompareExchangeValueFromSession(item.getKey(), sessionValueRef)) {
//            sessionValueRef.value = new CompareExchangeSessionValue(item.getKey(), 0, CompareExchangeValueState.NONE);
//            _state.put(item.getKey(), sessionValueRef.value);
//        }
//
//        sessionValueRef.value.delete(item.getIndex());
//    }
//
//    public void deleteCompareExchangeValue(String key, long index) {
//        if (key == null) {
//            throw new IllegalArgumentException("Key cannot be null");
//        }
//
//        Reference<CompareExchangeSessionValue> sessionValueRef = new Reference<>();
//        if (!tryGetCompareExchangeValueFromSession(key, sessionValueRef)) {
//            sessionValueRef.value = new CompareExchangeSessionValue(key, 0, CompareExchangeValueState.NONE);
//            _state.put(key, sessionValueRef.value);
//        }
//
//        sessionValueRef.value.delete(index);
//    }

    public function clear(): void
    {
        $this->state = [];
    }

//    protected <T> CompareExchangeValue<T> getCompareExchangeValueInternal(Class<T> clazz, String key) {
//        Reference<Boolean> notTrackedReference = new Reference<>();
//        CompareExchangeValue<T> v = getCompareExchangeValueFromSessionInternal(clazz, key, notTrackedReference);
//        if (!notTrackedReference.value) {
//            return v;
//        }
//
//        session.incrementRequestCount();
//
//        CompareExchangeValue<ObjectNode> value = session.getOperations().send(
//                new GetCompareExchangeValueOperation<>(ObjectNode.class, key, false), session.sessionInfo);
//        if (value == null) {
//            registerMissingCompareExchangeValue(key);
//            return null;
//        }
//
//        CompareExchangeSessionValue sessionValue = registerCompareExchangeValue(value);
//        if (sessionValue != null) {
//            return sessionValue.getValue(clazz, session.getConventions());
//        }
//
//        return null;
//    }
//
//    protected <T> Map<String, CompareExchangeValue<T>> getCompareExchangeValuesInternal(Class<T> clazz, String[] keys) {
//        Reference<Set<String>> notTrackedKeys = new Reference<>();
//        Map<String, CompareExchangeValue<T>> results = getCompareExchangeValuesFromSessionInternal(clazz, keys, notTrackedKeys);
//
//        if (notTrackedKeys.value == null || notTrackedKeys.value.isEmpty()) {
//            return results;
//        }
//
//        session.incrementRequestCount();
//
//        String[] keysArray = notTrackedKeys.value.toArray(new String[0]);
//        Map<String, CompareExchangeValue<ObjectNode>> values = session.getOperations().send(new GetCompareExchangeValuesOperation<>(ObjectNode.class, keysArray), session.sessionInfo);
//
//        for (String key : keysArray) {
//            CompareExchangeValue<ObjectNode> value = values.get(key);
//            if (value == null) {
//                registerMissingCompareExchangeValue(key);
//                results.put(key, null);
//                continue;
//            }
//
//            CompareExchangeSessionValue sessionValue = registerCompareExchangeValue(value);
//            results.put(value.getKey(), sessionValue.getValue(clazz, session.getConventions()));
//        }
//
//        return results;
//    }
//
//    protected <T> Map<String, CompareExchangeValue<T>> getCompareExchangeValuesInternal(Class<T> clazz, String startsWith, int start, int pageSize) {
//        session.incrementRequestCount();
//
//        Map<String, CompareExchangeValue<ObjectNode>> values = session.getOperations().send(
//                new GetCompareExchangeValuesOperation<ObjectNode>(ObjectNode.class, startsWith, start, pageSize), session.getSessionInfo());
//
//        Map<String, CompareExchangeValue<T>> results = new HashMap<>();
//
//        for (Map.Entry<String, CompareExchangeValue<ObjectNode>> keyValue : values.entrySet()) {
//
//            String key = keyValue.getKey();
//            CompareExchangeValue<ObjectNode> value = keyValue.getValue();
//
//            if (value == null) {
//                registerMissingCompareExchangeValue(key);
//                results.put(key, null);
//                continue;
//            }
//
//            CompareExchangeSessionValue sessionValue = registerCompareExchangeValue(value);
//            results.put(key, sessionValue.getValue(clazz, session.getConventions()));
//        }
//
//        return results;
//    }
//
//    public <T> CompareExchangeValue<T> getCompareExchangeValueFromSessionInternal(Class<T> clazz, String key, Reference<Boolean> notTracked) {
//        Reference<CompareExchangeSessionValue> sessionValueReference = new Reference<>();
//        if (tryGetCompareExchangeValueFromSession(key, sessionValueReference)) {
//            notTracked.value = false;
//            return sessionValueReference.value.getValue(clazz, session.getConventions());
//        }
//
//        notTracked.value = true;
//        return null;
//    }
//
//    public <T> Map<String, CompareExchangeValue<T>> getCompareExchangeValuesFromSessionInternal(Class<T> clazz, String[] keys, Reference<Set<String>> notTrackedKeys) {
//        notTrackedKeys.value = null;
//
//        Map<String, CompareExchangeValue<T>> results = new TreeMap<>(String::compareToIgnoreCase);
//
//        if (keys == null || keys.length == 0) {
//            return results;
//        }
//
//        for (String key : keys) {
//            Reference<CompareExchangeSessionValue> sessionValueRef = new Reference<>();
//            if (tryGetCompareExchangeValueFromSession(key, sessionValueRef)) {
//                results.put(key, sessionValueRef.value.getValue(clazz, session.getConventions()));
//                continue;
//            }
//
//            if (notTrackedKeys.value == null) {
//                notTrackedKeys.value = new TreeSet<>(String::compareToIgnoreCase);
//            }
//
//            notTrackedKeys.value.add(key);
//        }
//
//        return results;
//    }
//
//    public CompareExchangeSessionValue registerMissingCompareExchangeValue(String key) {
//        CompareExchangeSessionValue value = new CompareExchangeSessionValue(key, -1, CompareExchangeValueState.MISSING);
//        if (session.noTracking) {
//            return value;
//        }
//
//        _state.put(key, value);
//        return value;
//    }
//

    public function registerCompareExchangeValues(array $values): void
    {

    }
//    public void registerCompareExchangeValues(ObjectNode values) {
//        if (session.noTracking) {
//            return;
//        }
//
//        if (values != null) {
//            Iterator<Map.Entry<String, JsonNode>> fields = values.fields();
//            while (fields.hasNext()) {
//                Map.Entry<String, JsonNode> propertyDetails = fields.next();
//
//                registerCompareExchangeValue(
//                        CompareExchangeValueResultParser.getSingleValue(
//                                ObjectNode.class, (ObjectNode) propertyDetails.getValue(), false, session.getConventions()));
//            }
//        }
//    }
//
//    public CompareExchangeSessionValue registerCompareExchangeValue(CompareExchangeValue<ObjectNode> value) {
//        if (session.noTracking) {
//            return new CompareExchangeSessionValue(value);
//        }
//
//        CompareExchangeSessionValue sessionValue = _state.get(value.getKey());
//
//        if (sessionValue == null) {
//            sessionValue = new CompareExchangeSessionValue(value);
//            _state.put(value.getKey(), sessionValue);
//            return sessionValue;
//        }
//
//        sessionValue.updateValue(value, session.getConventions().getEntityMapper());
//
//        return sessionValue;
//    }
//
//    private boolean tryGetCompareExchangeValueFromSession(String key, Reference<CompareExchangeSessionValue> valueRef) {
//        CompareExchangeSessionValue value = _state.get(key);
//        valueRef.value = value;
//        return value != null;
//    }

    public function prepareCompareExchangeEntities(SaveChangesData $result): void
    {
        if (empty($this->state)) {
            return;
        }

        /**
         * @var string $key
         * @var CompareExchangeSessionValue $value
         */
        foreach ($this->state as $key => $value) {
            $command = $value->getCommand($this->session->getConventions());
            if ($command == null) {
                continue;
            }

            $result->addSessionCommand($command);
        }
    }

//    public void updateState(String key, long index) {
//        Reference<CompareExchangeSessionValue> sessionValueReference = new Reference<>();
//        if (!tryGetCompareExchangeValueFromSession(key, sessionValueReference)) {
//            return;
//        }
//
//        sessionValueReference.value.updateState(index);
//    }
//
//
}
