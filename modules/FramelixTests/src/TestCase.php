<?php

namespace Framelix\FramelixTests;

use Framelix\Framelix\Config;
use Framelix\Framelix\Date;
use Framelix\Framelix\DateTime;
use Framelix\Framelix\Db\Mysql;
use Framelix\Framelix\Db\MysqlStorableSchemeBuilder;
use Framelix\Framelix\Db\StorableSchema;
use Framelix\Framelix\ErrorCode;
use Framelix\Framelix\Exception;
use Framelix\Framelix\Form\Field;
use Framelix\Framelix\Form\Form;
use Framelix\Framelix\Html\HtmlAttributes;
use Framelix\Framelix\Html\Toast;
use Framelix\Framelix\Lang;
use Framelix\Framelix\Network\JsCall;
use Framelix\Framelix\Network\Request;
use Framelix\Framelix\Storable\Storable;
use Framelix\Framelix\Storable\User;
use Framelix\Framelix\Url;
use Framelix\Framelix\Utils\Buffer;
use Framelix\Framelix\View;
use Framelix\FramelixTests\Storable\TestStorable1;
use ReflectionClass;
use ReflectionUnionType;
use Throwable;

use function call_user_func_array;
use function file_exists;
use function file_put_contents;
use function get_class;
use function in_array;
use function is_array;
use function is_int;
use function is_string;
use function str_starts_with;
use function strlen;
use function strtoupper;
use function unlink;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * Does call all static and public methods of an object
     * This is solely for php error checks, not for logic tests
     * This also not covers all methods and some will throw errors
     * It should be the base tests, later you add manual logic tests
     * @param object $object
     * @param array|null $ignoreMethods
     * @return void
     */
    public function callMethodsGeneric(object $object, ?array $ignoreMethods = null): void
    {
        $reflection = new ReflectionClass($object);
        foreach ($reflection->getMethods() as $method) {
            if ($method->isAbstract() || $method->isPrivate() || $method->isProtected()) {
                continue;
            }
            $shortName = $method->getShortName();
            if (str_starts_with($shortName, "__")) {
                continue;
            }
            if ($ignoreMethods && in_array($shortName, $ignoreMethods)) {
                continue;
            }
            $args = [];
            foreach ($method->getParameters() as $parameter) {
                if ($parameter->isOptional()) {
                    break;
                }
                $value = null;
                $type = $parameter->getType();
                if ($type instanceof ReflectionUnionType) {
                    $type = $type->getTypes()[0];
                }
                $paramType = $type?->getName();
                switch ($paramType) {
                    case 'int':
                        $value = 1;
                        break;
                    case 'double':
                    case 'float':
                        $value = 1.22;
                        break;
                    case 'string':
                    case 'mixed':
                        $value = "test";
                        break;
                    case 'array':
                        $value = ["test"];
                        break;
                    case JsCall::class:
                        $value = new JsCall("testaction", null);
                        break;
                    case Date::class:
                        $value = Date::create("2000-01-01");
                        break;
                    case DateTime::class:
                        $value = DateTime::create("2000-01-01");
                        break;
                    case Storable::class:
                        $value = new TestStorable1();
                        break;
                    case HtmlAttributes::class:
                        $value = new HtmlAttributes();
                        break;
                    case Url::class:
                        $value = Url::create();
                        break;
                    case View::class:
                        $value = new View\Api();
                        break;
                    case Field::class:
                        $value = new Field\Text();
                        $value->name = "foobar";
                        break;
                }
                $args[] = $value;
            }
            Buffer::start();
            $method->invoke($object, ...$args);
            Buffer::get();
            $this->assertTrue(true);
        }
    }

    /**
     * Call all methods that each field must have
     * @param Field $field
     * @return void
     */
    public function callFormFieldDefaultMethods(Field $field): void
    {
        $testForm = new Form();
        $testForm->id = "test";

        $class = get_class($field);
        $clone = new $class();
        $clone->name = "foo";
        $testForm->addField($clone);

        // check required
        $clone->required = true;
        $this->assertSame(Lang::get('__framelix_form_validation_required__'), $clone->validate());

        $clone->getVisibilityCondition()->equal('foo', 'test');
        $this->assertFalse($clone->isVisible());
        // just calling validate to pass the default validation tests
        // could be anything (string/bool)
        $clone->validate();

        $oldPost = $_POST;
        $this->setSimulatedPostData([$clone->name => 'test']);
        $this->assertTrue($clone->isVisible());
        $this->setSimulatedPostData($oldPost);

        $clone->defaultValue = "bla";

        $this->assertIsArray($clone->jsonSerialize());
    }

    /**
     * Call all storable interface methods on given class name
     * @param string $className
     * @return void
     */
    public function callStorableInterfaceMethods(string $className): void
    {
        $schema = new StorableSchema($className);
        $property = $schema->createProperty('test');
        $this->assertNull(call_user_func_array([$className, "setupSelfStorableSchemaProperty"], [$property]));
        call_user_func_array([$className, "createFromDbValue"], ["foo"]);
        call_user_func_array([$className, "createFromFormValue"], ["foo"]);
    }

    /**
     * Assert storables default getter
     * @param Storable $storable
     * @return void
     */
    public function assertStorableDefaultGetters(Storable $storable): void
    {
        $this->assertIsBool($storable->isEditable());
        $this->assertIsBool($storable->isDeletable());
        $this->assertTrue(is_int($storable->getDbValue()) || $storable->getDbValue() === null);
        $this->assertIsString($storable->getHtmlString());
        $this->assertIsString($storable->getRawTextString());
        $this->assertIsString($storable->getSortableValue());
        $this->assertTrue($storable->getEditUrl() instanceof Url || $storable->getEditUrl() === null);
    }

    /**
     * Setup database for tests
     * @param bool $simulateDefaultConnection If true, this will add a connection id "default" based on "test"
     * @return void
     */
    public function setupDatabase(bool $simulateDefaultConnection = false): void
    {
        if ($simulateDefaultConnection) {
            Config::set('database[default]', Config::get('database[test]'));
        }
        $this->cleanupDatabase();
        $db = Mysql::get('test');
        $builder = new MysqlStorableSchemeBuilder($db);
        $queries = $builder->getQueries();
        foreach ($queries as $queryData) {
            $db->query($queryData['query']);
        }
    }

    /**
     * Setup database after tests
     * Drop and recreate db
     * @return void
     */
    public function cleanupDatabase(): void
    {
        $db = Mysql::get('test');
        $db->query("DROP DATABASE `{$db->connectionConfig['database']}`");
        $db->query("CREATE DATABASE `{$db->connectionConfig['database']}`");
        $db->query("USE `{$db->connectionConfig['database']}`");
    }

    /**
     * Add simulated file in $_FILES
     * @param string $name
     * @param string $filedata
     * @param bool $isMultiple This will add the same file 2 times to simulate multiple upload
     * @param string $filetype
     * @param int $errorCode
     * @return void
     */
    public function addSimulatedFile(
        string $name,
        string $filedata,
        bool $isMultiple,
        string $filetype = '',
        int $errorCode = 0
    ): void {
        $this->removeSimulatedFile($name);
        $tmpName = __DIR__ . "/../tmp/" . $name . ".txt";
        if ($isMultiple) {
            $_FILES[$name]['name'][0] = $name;
            $_FILES[$name]['tmp_name'][0] = $tmpName;
            $_FILES[$name]['size'][0] = (string)strlen($filedata);
            $_FILES[$name]['type'][0] = $filetype;
            $_FILES[$name]['error'][0] = $errorCode;

            $_FILES[$name]['name'][1] = $name;
            $_FILES[$name]['tmp_name'][1] = $tmpName;
            $_FILES[$name]['size'][1] = (string)strlen($filedata);
            $_FILES[$name]['type'][1] = $filetype;
            $_FILES[$name]['error'][0] = $errorCode;
        } else {
            $_FILES[$name]['name'] = $name;
            $_FILES[$name]['tmp_name'] = $tmpName;
            $_FILES[$name]['size'] = (string)strlen($filedata);
            $_FILES[$name]['type'] = $filetype;
            $_FILES[$name]['error'] = $errorCode;
        }
        if (!$errorCode) {
            file_put_contents($tmpName, $filedata);
        }
    }

    /**
     * Remove simulated file in $_FILES
     * @param string $name
     * @return void
     */
    public function removeSimulatedFile(string $name): void
    {
        $tmpName = __DIR__ . "/../tmp/" . $name . ".txt";
        if (file_exists($tmpName)) {
            unlink($tmpName);
        }
        if (isset($_FILES[$name]['name'])) {
            if (is_array($_FILES[$name]['name'])) {
                foreach ($_FILES[$name]['name'] as $key => $filename) {
                    if (file_exists($_FILES[$name]['tmp_name'][$key])) {
                        unlink($_FILES[$name]['tmp_name'][$key]);
                    }
                }
                unset($_FILES[$name]);
            } else {
                if (file_exists($_FILES[$name]['tmp_name'])) {
                    unlink($_FILES[$name]['tmp_name']);
                }
                unset($_FILES[$name]);
            }
        }
    }

    /**
     * Set simulated header
     * @param string $name
     * @param string|null $value
     * @return void
     */
    public function setSimulatedHeader(string $name, ?string $value): void
    {
        $_SERVER[strtoupper($name)] = $value;
    }

    /**
     * Set simulated body data context
     * @param array $data
     * @return void
     */
    public function setSimulatedBodyData(array $data): void
    {
        Request::$requestBodyData['data'] = $data;
    }

    /**
     * Set simulated post data context
     * @param array $data
     * @return void
     */
    public function setSimulatedPostData(array $data): void
    {
        $_POST = $data;
    }

    /**
     * Set simulated get data context
     * @param array $data
     * @return void
     */
    public function setSimulatedGetData(array $data): void
    {
        $url = Url::create();
        $url->removeParameters();
        $url->addParameters($data);
        $_SERVER['REQUEST_URI'] = $url->getPathAndQueryString();
        if ($url->getHash()) {
            $_SERVER['REQUEST_URI'] .= "#" . $url->getHash();
        }
        $_GET = $data;
    }

    /**
     * Set server current url for unit test
     * @param string|Url $url
     * @return void
     */
    public function setSimulatedUrl(string|Url $url): void
    {
        if (is_string($url)) {
            $url = Url::create($url);
        } else {
            $url = clone $url;
        }
        $host = $url->urlData['host'] ?? 'localhost';
        if ($url->getUsername()) {
            $host = $url->getUsername() . ":" . $url->getPassword() . "@" . $host;
        }
        if ($url->getPort()) {
            $host .= ":" . $url->getPort();
        }
        $_SERVER['HTTPS'] = (($url->urlData['scheme'] ?? null) === "https") ? "on" : "off";
        $_SERVER['HTTP_HOST'] = $host;
        unset($url->urlData['scheme'], $url->urlData['host']);
        $_SERVER['REQUEST_URI'] = $url->getPath();
        if ($url->getHash()) {
            $_SERVER['REQUEST_URI'] .= "#" . $url->getHash();
        }
        $this->setSimulatedGetData($url->urlData['queryParameters'] ?? []);
    }

    /**
     * Set simulated user
     * @param mixed $roles
     *  null = No user
     *  true = A user without roles
     *  array = A user with given roles
     * @return void
     */
    public function setSimulatedUser(mixed $roles): void
    {
        if ($roles === null) {
            User::setCurrentUser(null);
            return;
        }
        $user = new User();
        if (is_array($roles)) {
            $user->roles = $roles;
        }
        User::setCurrentUser($user);
    }

    /**
     * Assert a success toast to be queued
     * Does reset the toast cache after calling this
     * @return void
     */
    public function assertToastSuccess(): void
    {
        $this->assertTrue(Toast::hasSuccess(), 'Success Toast required');
        Toast::getQueueMessages(true);
    }

    /**
     * Assert a warning toast to be queued
     * Does reset the toast cache after calling this
     * @return void
     */
    public function assertToastWarning(): void
    {
        $this->assertTrue(Toast::hasWarning(), 'Warning Toast required');
        Toast::getQueueMessages(true);
    }

    /**
     * Assert a error toast to be queued
     * Does reset the toast cache after calling this
     * @return void
     */
    public function assertToastError(): void
    {
        $this->assertTrue(Toast::hasError(), 'Error Toast required');
        Toast::getQueueMessages(true);
    }

    /**
     * Assert a info toast to be queued
     * Does reset the toast cache after calling this
     * @return void
     */
    public function assertToastInfo(): void
    {
        $this->assertTrue(Toast::hasInfo(), 'Info Toast required');
        Toast::getQueueMessages(true);
    }

    /**
     * Assert given framelix error code
     * @param ErrorCode $expected
     * @param mixed $actual
     * @return void
     */
    public function assertFramelixErrorCode(ErrorCode $expected, mixed $actual): void
    {
        $actualCode = 'NO_FRAMELIX_ERROR_CODE';
        $message = '';
        if ($actual instanceof Exception) {
            $actualCode = $actual->framelixErrorCode->name;
        }
        if ($actual instanceof Throwable) {
            $message = $actual->getMessage();
        }
        $this->assertSame("ErrorCode->" . $expected->name, "ErrorCode->" . $actualCode, $message);
    }
}