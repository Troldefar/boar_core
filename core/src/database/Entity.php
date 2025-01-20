<?php

/**
|----------------------------------------------------------------------------
| Application entities
|----------------------------------------------------------------------------
| Model extender - This is where models interact with the database
| 
| @author RE_WEB
| @package \app\core\src
|
*/

namespace app\core\src\database;

use \app\core\src\attributes\Metadata;

use \app\core\src\database\table\Table;

use \app\core\src\exceptions\EmptyException;
use \app\core\src\exceptions\ForbiddenException;
use app\core\src\exceptions\InvalidTypeException;
use \app\core\src\exceptions\NotFoundException;

use \app\core\src\miscellaneous\CoreFunctions;

use \app\core\src\traits\entity\EntityQueryTrait;
use \app\core\src\traits\entity\EntityMagicMethodTrait;
use \app\core\src\traits\entity\EntityHTTPMethodTrait;
use \app\core\src\traits\entity\EntityRelationsTrait;

abstract class Entity {

    use EntityQueryTrait;
    use EntityMagicMethodTrait;
    use EntityHTTPMethodTrait;
    use EntityRelationsTrait;

    private const INVALID_ENTITY_SAVE   = 'Entity has not yet been properly stored, did you call this method before ->save() ?';
    private const INVALID_ENTITY_STATUS = 'This entity does not have a status';
    private const INVALID_ENTITY_DATA   = 'Data can not be empty';
    private const INVALID_ENTITY_KEY    = 'Invalid entity key';
    private const INVALID_ENTITY_STATIC_METHOD = 'Invalid static method';
    private const INVALID_ENTITY_METHOD = 'Invalid non static method method';
    private const INITIAL_CLIENT_REQUEST_CACHED_POST_CREATED_TIMESTAMP = 'InitialClientRequestCreatedTimestamp';

    private $key;
    protected array $data = [];
    protected array $additionalConstructorMethods = [];
    
    private array $availableCallMethods = ['crud'];
    
    abstract protected function getKeyField()  : string;
    abstract protected function getTableName() : string;
    
    public function __construct($data = null) {
        $this->set($data);
        if ($this->exists()) $this->checkAdditionalConstructorMethods();
    }

    private function checkAdditionalConstructorMethods(): void {
        array_map(function($method) {
            $this->data[$method] = $this->dispatchMethod($method);
        }, $this->additionalConstructorMethods);
    }

    public function set($data = null): Entity {
        $key = $this->getKeyField();

        if ($data !== null && gettype($data) !== "array") $data = [$key => $data];

        if(isset($data[$key])) {
            $exists = $this->getQueryBuilder()->fetchRow([$key => $data[$key]]);
            if(empty($exists)) goto proceed;

            $this->setKey($exists->{$key});
            $this->setData((array)$exists);
            unset($this->data[$key]);
            unset($data[$key]);
        }

        proceed:

        $this->data = array_merge($this->data, $data ?? []);
        
        return $this;
    }

    protected function setKey(string $key): void {
        $this->key = $key;
    }

    protected function setKeyValuePair(string $key, mixed $value) {
        $this->data[$key] = $value;
    }

    public function key(): ?string {
        return $this->key;
    }

    public function exists(): bool {
        return $this->key !== null;
    }

    public function setData(array $data) {
        $this->data = $data;
    }

    private function checkClientCachedPOSTCreatedTimestampField(): void {
        if (!$this->propertyExists(self::INITIAL_CLIENT_REQUEST_CACHED_POST_CREATED_TIMESTAMP)) return;

        $initialClientRequestCreatedTimestamp = $this->get(self::INITIAL_CLIENT_REQUEST_CACHED_POST_CREATED_TIMESTAMP);
        $date = date('Y-m-d H:i:s', $initialClientRequestCreatedTimestamp);

        $this->set([Table::CREATED_AT_COLUMN => $date]);
        $this->appendHistory([Table::CREATED_AT_COLUMN . ' field was changed because InitialClientRequestCreatedTimestamp was set and set to: ' . $date]);
    }

    public function save(bool $addMetaData = false): self {
        $this->checkClientCachedPOSTCreatedTimestampField();

        if ($addMetaData) $this->addMetaData($this->data);
        if ($this->exists()) return $this->patchEntity();
        if (empty($this->data)) throw new EmptyException();

        return $this->createEntity();
    }

    public function setAndSave(array $data, $addMetaData = false): self {
        $this->setData($data);
        $this->save($addMetaData);
        return $this;
    }

    public function get(string $key): mixed {
        return $this->data[$key] ?? false; 
    }

    public function propertyExists(string $property): bool {
        return isset($this->data[$property]);
    }

    public function getData(): array {
        return $this->data;
    }

    public function getFrontendFriendlyData() {
        $toBeDisplayed = $this->getData();
        unset($toBeDisplayed[$this->getKeyField()]);
        return $toBeDisplayed;
    }

    public function checkAllowSave(): void {
        if (!$this->exists()) throw new EmptyException(self::INVALID_ENTITY_SAVE);
    }

    public function setTmpProperties(array $entityProperties): void {
        $this->set($entityProperties);
    }

    private function checkMethodValidity(string $method) {
        if (!method_exists($this, $method)) throw new NotFoundException(self::INVALID_ENTITY_METHOD);
    }

    public function setAllowedHTTPMethods() {
		$this->setValidHTTPMethods($this->ALLOWED_HTTP_METHODS);
	}

    /**
     * Dispatcher for entity methods
     * @throws NotFoundException
     */
    public function dispatchMethod(string $method, mixed $arguments = []) {
        $this->checkMethodValidity($method);

        return $this->{$method}($arguments);
    }

    public function dispatchHTTPMethod(string $httpRequestEntityMethod, mixed $httpBody) {
        $this->setAllowedHTTPMethods();
        $this->validateHTTPAction($httpBody, $httpRequestEntityMethod);

        return $this->dispatchMethod($httpRequestEntityMethod, $httpBody);
    }

    public function requireExistence(): void {
        if (!$this->exists()) app()->getResponse()->notFound();
    }

    private function checkAvailableCallMethods(string $method): bool {
        return in_array($method, $this->availableCallMethods);
    }

    private function checkOverloadArgumentCount(int $count, array $possibleLengthRequirements): void {
        if (!in_array($count, $possibleLengthRequirements)) 
            throw new ForbiddenException('Invalid parameter numbers');
    }

    #[Metadata(type: 'method', description: 'Fetch entities default created at column')]
    public function getCreatedTimestamp(string $date = ''): ?string {
        if (!$this->propertyExists(Table::CREATED_AT_COLUMN)) throw new InvalidTypeException(Table::CREATED_AT_COLUMN . ' is not defined');

        return date('d-m-Y H:i', strtotime(($date !== '' ? $date : $this->get(Table::CREATED_AT_COLUMN))));
    }

    #[Metadata(type: 'method', description: 'Fetch entities default sort order column')]
    public function getSortOrder(): ?int {
        if (!$this->propertyExists(Table::SORT_ORDER_COLUMN)) throw new InvalidTypeException(Table::SORT_ORDER_COLUMN . ' is not defined');

        return $this->get(Table::SORT_ORDER_COLUMN) ?? null;
    }

    #[Metadata(type: 'method', description: 'Fetch entities languge table')]
    protected function languagePivot(): string {
        return 'entity_language';
    }

}