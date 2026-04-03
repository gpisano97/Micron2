# Micron2 Framework - Complete Documentation

## Table of Contents

1. [Framework Overview](#framework-overview)
2. [Core Classes](#core-classes)
3. [HTTP Classes](#http-classes)
4. [Dependency Injection](#dependency-injection)
5. [Controllers & Routing](#controllers--routing)
6. [Middlewares](#middlewares)
7. [Usage Examples](#usage-examples)
8. [Quick Start Guide](#quick-start-guide)
9. [Api Reference](./API_REFERENCE.md)

---

## Framework Overview

**Micron2** is a lightweight PHP web application framework built on the middleware pattern. It provides:

- **Dependency Injection (DI) Container**: Automatic resolution of dependencies
- **Attribute-based Routing**: Define routes using PHP attributes
- **Middleware Pipeline**: Chain middleware for request processing
- **HTTP Abstraction**: Easy-to-use HTTP request/response objects
- **CORS Support**: Built-in Cross-Origin Resource Sharing handling
- **Configuration Management**: JSON-based configuration loading

### Architecture

The framework uses a **middleware chain pattern** where each middleware can process the request and pass it to the next middleware. The flow is:

1. Request enters → First Middleware
2. → Chain through middlewares
3. → Route Matching & Controller Execution
4. → Response flows back through middlewares
5. → Response sent to client

---

## Core Classes

### WebApplicationBuilder

**Purpose**: Constructs and configures the web application.

**Location**: `Micron2/Core/WebApplicationEngine/WebApplicationBuilder.php`

**Properties**:
- `_dependencyRegister: DependencyRegister` - Manages dependencies
- `_httpContext: HttpContext` - Current HTTP request/response context
- `_configurationsPath: string` - Path to configuration files
- `_scopedWithoutDI: array<callable>` - Custom service constructors

**Methods**:

```php
public function __construct()
```
Initializes the builder and automatically captures HTTP request data from PHP globals.

```php
public function AddScoped(string $className): void
```
Register a class to be created once per request (scoped lifetime). The class constructor parameters are automatically resolved from the DI container.

**Parameters**:
- `$className`: Fully qualified class name (e.g., `MyService::class`)

```php
public function AddScopedWithoutDI(callable $objectConstructor): void
```
Register a custom factory function for creating instances without automatic DI resolution.

**Parameters**:
- `$objectConstructor`: Callable that receives `(HttpContext $context, ?AppConfiguration $config)` and returns an object

```php
public function AddConfigurations(string $path): void
```
Load JSON configuration files from a directory.

**Parameters**:
- `$path`: Directory path containing JSON files (e.g., `$_SERVER['DOCUMENT_ROOT']`)

```php
public function AddCors(CorsHandlerSettings $settings): void
```
Enable CORS (Cross-Origin Resource Sharing) with specified settings.

**Parameters**:
- `$settings`: CORS configuration object

```php
public function Build(): WebApplication
```
Build and return the configured `WebApplication` instance.

**Returns**: `WebApplication` object ready to start

---

### WebApplication

**Purpose**: Main application runtime that executes the middleware chain.

**Location**: `Micron2/Core/WebApplicationEngine/WebApplication.php`

**Properties**:
- `_firstHandler: IMiddleware` - Entry point of middleware chain
- `_lastHandler: ?IMiddleware` - Last middleware in chain
- `_endpointsAdded: bool` - Flag tracking endpoint middleware addition
- `_register: DependencyRegister` - Dependency container
- `_httpContext: HttpContext` - HTTP context for current request

**Methods**:

```php
public function __construct(DependencyRegister $register, HttpContext $context)
```
Initialize the application with dependencies and HTTP context.

```php
public function AddMiddleware(string $middlewareClassName): void
```
Add a custom middleware to the pipeline.

**Parameters**:
- `$middlewareClassName`: Fully qualified middleware class name

```php
public function AddEndpoints(): void
```
Add the endpoint handler middleware (routes requests to controllers). Should be called before `Start()`.

```php
public function Start(): void
```
Execute the middleware pipeline and send the response to the client.

---

### HttpContext

**Purpose**: Container for HTTP request and response objects.

**Location**: `Micron2/Core/Classes/HttpContext.php`

**Properties**:
- `request: HttpRequest` - The incoming HTTP request
- `response: HttpResponse` - The HTTP response to send back

**Methods**:

```php
public function __construct()
```
Initialize with empty `HttpRequest` and `HttpResponse` objects.

---

### HttpRequest

**Purpose**: Represents the incoming HTTP request.

**Location**: `Micron2/Core/Classes/HttpRequest.php`

**Properties**:
- `uri: string` - Request URI
- `method: HttpMethod` - HTTP method (GET, POST, PUT, DELETE, etc.)
- `requestBody: string` - Raw request body (for JSON/XML)
- `headers: array<string, string>` - HTTP headers
- `post: array` - POST form data (`$_POST`)
- `files: array` - Uploaded files (`$_FILES`)

**Methods**:

```php
public function GetRequestCreatedAt(): DateTime
```
Get the timestamp when the request was created.

**Returns**: `DateTime` object

---

### HttpResponse

**Purpose**: Represents the HTTP response to send to the client.

**Location**: `Micron2/Core/HttpResponse.php`

**Properties**:
- `statusCode: int` - HTTP status code (200, 404, 500, etc.)
- `content: mixed` - Response body (will be JSON-encoded if array/object)
- `headers: array<HttpHeader|string, string>` - Response headers

**Methods**:

```php
public function __construct(int $statusCode, mixed $content, ?HttpContentType $contentType = null)
```
Create a response with status code, content, and optional content type.

```php
public function addHeader(HttpHeader $header, string $value): self
```
Add a header to the response. Returns `$this` for method chaining.

**Parameters**:
- `$header`: `HttpHeader` enum value
- `$value`: Header value

**Static Methods (HTTP Success Responses - 2xx)**:

```php
public static function HttpOk200(mixed $content, ?HttpContentType $type = null): HttpResponse
```
200 OK response.

```php
public static function HttpCreated201(mixed $content, ?HttpContentType $type = null): HttpResponse
```
201 Created response.

```php
public static function HttpAccepted202(mixed $content, ?HttpContentType $type = null): HttpResponse
```
202 Accepted response.

```php
public static function HttpNoContent204(mixed $content = '', ?HttpContentType $type = null): HttpResponse
```
204 No Content response.

**Static Methods (Redirection - 3xx)**:

```php
public static function HttpMovedPermanently301(mixed $content, ?HttpContentType $type = null): HttpResponse
public static function HttpFound302(mixed $content, ?HttpContentType $type = null): HttpResponse
public static function HttpSeeOther303(mixed $content, ?HttpContentType $type = null): HttpResponse
public static function HttpNotModified304(mixed $content = '', ?HttpContentType $type = null): HttpResponse
public static function HttpTemporaryRedirect307(mixed $content, ?HttpContentType $type = null): HttpResponse
```

**Static Methods (Client Errors - 4xx)**:

```php
public static function HttpBadRequest400(mixed $content, ?HttpContentType $type = null): HttpResponse
public static function HttpUnauthorized401(mixed $content, ?HttpContentType $type = null): HttpResponse
public static function HttpForbidden403(mixed $content, ?HttpContentType $type = null): HttpResponse
public static function HttpNotFound404(mixed $content, ?HttpContentType $type = null): HttpResponse
public static function HttpMethodNotAllowed405(mixed $content, ?HttpContentType $type = null): HttpResponse
public static function HttpConflict409(mixed $content, ?HttpContentType $type = null): HttpResponse
public static function HttpTooManyRequests429(mixed $content, ?HttpContentType $type = null): HttpResponse
```

**Static Methods (Server Errors - 5xx)**:

```php
public static function HttpInternalServerError500(mixed $content, ?HttpContentType $type = null): HttpResponse
public static function HttpNotImplemented501(mixed $content, ?HttpContentType $type = null): HttpResponse
public static function HttpBadGateway502(mixed $content, ?HttpContentType $type = null): HttpResponse
public static function HttpServiceUnavailable503(mixed $content, ?HttpContentType $type = null): HttpResponse
public static function HttpGatewayTimeout504(mixed $content, ?HttpContentType $type = null): HttpResponse
```

---

### AppConfiguration

**Purpose**: Load and store application configuration from JSON files.

**Location**: `Micron2/Core/Classes/AppConfiguration.php`

**Properties**:
- `configuration: array<string, mixed>` - Merged configuration data from all JSON files

**Methods**:

```php
public function __construct(string $configPath)
```
Load all JSON files from the given directory and merge them into the configuration array.

**Parameters**:
- `$configPath`: Path to directory containing JSON configuration files

---

## HTTP Classes

### HttpContentType (Enum)

**Purpose**: Define standard MIME types for HTTP responses.

**Location**: `Micron2/Core/Classes/HttpTypes.php`

**Values**:
- Text: `TEXT_PLAIN`, `TEXT_HTML`, `TEXT_CSS`, `TEXT_CSV`, `TEXT_XML`
- JSON/JavaScript: `APPLICATION_JSON`, `APPLICATION_JAVASCRIPT`
- Form: `FORM_URLENCODED`, `FORM_DATA`
- Binary: `OCTET_STREAM`, `PDF`, `ZIP`
- Images: `IMAGE_PNG`, `IMAGE_JPEG`, `IMAGE_GIF`, `IMAGE_SVG`, `IMAGE_WEBP`
- Audio/Video: `AUDIO_MPEG`, `VIDEO_MP4`, `VIDEO_WEBM`
- XML/SOAP: `APPLICATION_XML`, `APPLICATION_SOAP_XML`
- Fonts: `FONT_WOFF`, `FONT_WOFF2`

**Usage**:
```php
$response = HttpResponse::HttpOk200($data, HttpContentType::APPLICATION_JSON);
```

### HttpMethod (Enum)

**Purpose**: Define HTTP request methods.

**Location**: `Micron2/Core/Classes/HttpTypes.php`

**Values**: `GET`, `POST`, `PUT`, `DELETE`, `PATCH`, `HEAD`, `OPTIONS`, `TRACE`, `CONNECT`

---

### HttpHeader (Enum)

**Purpose**: Standard HTTP header names.

**Location**: `Micron2/Core/Classes/HttpTypes.php`

**Common Values**:
- `ACCEPT`, `ACCEPT_ENCODING`, `ACCEPT_LANGUAGE`
- `CONTENT_TYPE`, `CONTENT_LENGTH`, `CONTENT_DISPOSITION`
- `AUTHORIZATION`, `WWW_AUTHENTICATE`
- `ORIGIN`, `ACCESS_CONTROL_ALLOW_ORIGIN`, `ACCESS_CONTROL_ALLOW_METHODS`
- `USER_AGENT`, `REFERER`, `SERVER`

---

## Dependency Injection

### DependencyRegister

**Purpose**: Store and manage dependencies for the application.

**Location**: `Micron2/Core/WebApplicationEngine/DependencyRegister.php`

**Properties**:
- `_createdScopedDependency: array` - Already instantiated scoped dependencies (per-request)
- `_toCreateScopedDependency: array` - Classes awaiting instantiation
- `_transientDependency: array` - Transient dependencies (created fresh each time)

### DependencyResolver

**Purpose**: Automatically resolve and instantiate class dependencies using reflection.

**Location**: `Micron2/Core/WebApplicationEngine/DependencyRegister.php`

**Static Methods**:

```php
public static function ResolveScoped(DependencyRegister $register): void
```
Instantiate all registered scoped dependencies by analyzing their constructors and resolving dependencies recursively.

**Throws**: `Exception` if a dependency cannot be resolved or circular dependencies exist.

```php
public static function ResolveExternal(DependencyRegister $register, string $className): object
```
Resolve a single external class (like middleware) using the registered dependencies.

**Parameters**:
- `$className`: Fully qualified class name

**Returns**: Instantiated object

**Throws**: `Exception` if dependencies cannot be resolved

---

## Controllers & Routing

### Controller Attribute

**Purpose**: Mark a class as a controller and define a route prefix.

**Location**: `Micron2/Core/Controllers/Attributes/Controller.php`

**Usage**:
```php
#[Controller("api/users")]
class UserController {
    // methods...
}
```

**Parameters**:
- `prefix` (optional): Route prefix applied to all methods (default: empty string)

---

### Route Attributes

Define HTTP methods and paths for controller methods.

**Location**: `Micron2/Core/Controllers/Attributes/Controller.php`

#### Get

```php
#[Get("/{id}")]
public function getUser(int $id) { }
```

#### Post

```php
#[Post("/create")]
public function createUser() { }
```

#### Put

```php
#[Put("/{id}")]
public function updateUser(int $id) { }
```

#### Delete

```php
#[Delete("/{id}")]
public function deleteUser(int $id) { }
```

#### Patch

```php
#[Patch("/{id}")]
public function patchUser(int $id) { }
```

**Route Matching**:
- Segments wrapped in `{paramName}` are captured as URI parameters
- Query parameters are automatically extracted from the query string
- Static segments must match exactly

---

### Parameter Binding Attributes

Control where controller method parameters come from.

**Location**: `Micron2/Core/Controllers/Attributes/Controller.php`

#### FromPath

Bind parameter from URI path segment:
```php
public function getUser(#[FromPath] int $id) { }
```

#### FromQuery

Bind parameter from query string:
```php
public function search(#[FromQuery] string $q) { }
```

#### FromBody

Bind parameter from request body (JSON):
```php
public function createUser(#[FromBody] User $user) { }
```

#### FromFormData

Bind parameter from form data (POST/multipart):
```php
public function submitForm(#[FromFormData] FormData $data) { }
```

---

### Parameter Binding Details

**Scalar Types**: Automatically cast and bound by type
```php
public function example(int $id, string $name, float $price) { }
```

**Object Types**: Hydrated from request data by matching property names
```php
class UserDTO {
    public string $name;
    public string $email;
}

#[Post("/")]
public function create(#[FromBody] UserDTO $user) { }
```

---

### ControllerDiscovery

**Purpose**: Scan all registered classes and extract route metadata using reflection.

**Location**: `Micron2/Core/Controllers/Modules/ControllerDiscovery.php`

**Static Methods**:

```php
public static function scan(): RouteMap
```
Analyze all declared classes for `Controller` attributes and route methods. Returns a map of all discovered routes.

---

### RouteMatcher

**Purpose**: Match incoming HTTP requests to registered routes.

**Location**: `Micron2/Core/Controllers/Modules/RouteMatcher.php`

**Static Methods**:

```php
public static function match(HttpContext $context, RouteMap $map): ?MatchedRoute
```
Find a route matching the request method and URI.

**Parameters**:
- `$context`: Current HTTP context
- `$map`: Route map from discovery

**Returns**: `MatchedRoute` if found, `null` otherwise

---

### ControllerInvoker

**Purpose**: Execute a matched controller method with resolved dependencies and parameters.

**Location**: `Micron2/Core/Controllers/Modules/ControllerInvoker.php`

**Static Methods**:

```php
public static function invoke(
    DependencyRegister $register, 
    MatchedRoute $matchedRouteData, 
    HttpContext $context
): array|object|string|int|float
```
Instantiate the controller, bind parameters, and invoke the method.

**Returns**: The controller method's return value

---

## Middlewares

### IMiddleware Interface

**Purpose**: Define the contract for middleware.

**Location**: `Micron2/Core/Middlewares/MiddlewareInterface.php`

**Methods**:

```php
function setNext(IMiddleware $nextMiddleware): IMiddleware
```
Set the next middleware in the chain.

```php
function handle(HttpContext $context): HttpContext | HttpResponse | null
```
Process the request and pass to next middleware.

```php
function next(HttpContext $context): HttpContext | HttpResponse | null
```
Call the next middleware in the chain.

---

### AMiddleware Abstract Class

**Purpose**: Base class for custom middleware with built-in chain handling.

**Location**: `Micron2/Core/Middlewares/MiddlewareInterface.php`

**Methods**:

```php
public function handle(HttpContext $context): HttpContext | HttpResponse | null
```
Default implementation passes to next middleware. Override to add custom logic.

```php
protected function next(HttpContext $context): HttpContext | HttpResponse | null
```
Call this to execute the next middleware in the chain.

---

### Custom Middleware Example

```php
class LoggingMiddleware extends AMiddleware {
    public function handle(HttpContext $context): HttpContext | HttpResponse | null {
        // Log incoming request
        echo "-> {$context->request->method} {$context->request->uri}";
        
        // Pass to next middleware
        $result = $this->next($context);
        
        // Log response
        if ($result instanceof HttpResponse) {
            echo " [{$result->statusCode}]";
        }
        
        return $result;
    }
}
```

---

## Usage Examples

### Example 1: Basic Setup

**index.php**:
```php
<?php
require_once "Micron2/Micron2.php";

// Create app builder
$appBuilder = new WebApplicationBuilder();

// Register services
$appBuilder->AddScoped(UserService::class);
$appBuilder->AddScoped(DatabaseConnection::class);

// Load configuration
$appBuilder->AddConfigurations($_SERVER['DOCUMENT_ROOT']);

// Setup CORS
$appBuilder->AddCors(new CorsHandlerSettings());

// Build the application
$app = $appBuilder->Build();

// Add endpoints through route discovery
$app->AddEndpoints();

// Add custom middleware
$app->AddMiddleware(AuthenticationMiddleware::class);
$app->AddMiddleware(LoggingMiddleware::class);

// Start the application
$app->Start();
```

---

### Example 2: Creating a Controller

**App/Controllers/ProductController.php**:
```php
<?php
require_once "Micron2/Micron2.php";
require_once "App/Services/ProductService.php";

#[Controller("api/products")]
class ProductController {
    private ProductService $_service;
    private HttpContext $_context;

    public function __construct(ProductService $service, HttpContext $context) {
        $this->_service = $service;
        $this->_context = $context;
    }

    // GET /api/products/{id}
    #[Get("/{id}")]
    public function getProduct(#[FromPath] int $id) {
        $product = $this->_service->findById($id);
        
        if (!$product) {
            return HttpResponse::HttpNotFound404("Product not found");
        }
        
        return HttpResponse::HttpOk200(
            $product,
            HttpContentType::APPLICATION_JSON
        );
    }

    // POST /api/products
    #[Post("/")]
    public function createProduct(#[FromBody] CreateProductDTO $dto) {
        $product = $this->_service->create($dto->name, $dto->price);
        
        return HttpResponse::HttpCreated201(
            $product,
            HttpContentType::APPLICATION_JSON
        );
    }

    // PUT /api/products/{id}
    #[Put("/{id}")]
    public function updateProduct(#[FromPath] int $id, #[FromBody] UpdateProductDTO $dto) {
        $product = $this->_service->update($id, $dto->name, $dto->price);
        
        return HttpResponse::HttpOk200($product);
    }

    // DELETE /api/products/{id}
    #[Delete("/{id}")]
    public function deleteProduct(#[FromPath] int $id) {
        $this->_service->delete($id);
        
        return HttpResponse::HttpNoContent204();
    }
}
```

---

### Example 3: Creating a Service

**App/Services/ProductService.php**:
```php
<?php

class ProductService {
    private HttpContext $_context;
    private AppConfiguration $_config;

    public function __construct(HttpContext $context, AppConfiguration $config) {
        $this->_context = $context;
        $this->_config = $config;
    }

    public function findById(int $id) {
        // Fetch from database using $this->_config
        return ["id" => $id, "name" => "Sample Product"];
    }

    public function create(string $name, float $price) {
        return ["id" => 1, "name" => $name, "price" => $price];
    }

    public function update(int $id, string $name, float $price) {
        return ["id" => $id, "name" => $name, "price" => $price];
    }

    public function delete(int $id) {
        // Delete from database
    }
}
```

---

### Example 4: Custom Middleware

**App/Middlewares/ResponseWrapperMiddleware.php**:
```php
<?php
require_once "Micron2/Micron2.php";

final class ResponseWrapperMiddleware extends AMiddleware {
    public function handle(HttpContext $context): HttpContext | HttpResponse | null {
        // Get response from next middleware
        $response = $this->next($context);
        
        if ($response === null) {
            return HttpResponse::HttpInternalServerError500("No response");
        }
        
        // Wrap the response
        if ($response instanceof HttpResponse) {
            $wrapped = [
                "success" => $response->statusCode >= 200 && $response->statusCode < 300,
                "data" => $response->content,
                "timestamp" => date('c')
            ];
            
            $response->content = $wrapped;
            return $response;
        }
        
        return $response;
    }
}
```

---

### Example 5: Request with Multiple Parameters

```php
#[Controller("api/orders")]
class OrderController {
    // GET /api/orders/user/123?status=pending&limit=10
    #[Get("/user/{userId}")]
    public function getUserOrders(
        #[FromPath] int $userId,
        #[FromQuery] string $status = "all",
        #[FromQuery] int $limit = 20
    ) {
        // userId = 123 (from path)
        // status = "pending" (from query)
        // limit = 10 (from query)
        
        return HttpResponse::HttpOk200([
            "userId" => $userId,
            "status" => $status,
            "limit" => $limit
        ]);
    }
}
```

---

### Example 6: Custom DI Registration

```php
$appBuilder->AddScopedWithoutDI(function (HttpContext $context, AppConfiguration $config) {
    $apiKey = $config->configuration['api']['key'] ?? null;
    
    return new ExternalApiClient(
        "https://api.example.com",
        $apiKey
    );
});
```

---

## Quick Start Guide

### Step 1: Start XAMPP Services
- Ensure Apache and MySQL are running

### Step 2: Create a Controller

Create `App/Controllers/HelloController.php`:
```php
<?php
require_once "Micron2/Micron2.php";

#[Controller("api")]
class HelloController {
    public function __construct(private HttpContext $context) {}

    #[Get("/hello")]
    public function hello() {
        return HttpResponse::HttpOk200(["message" => "Hello, World!"]);
    }

    #[Post("/echo")]
    public function echo(#[FromBody] EchoRequest $request) {
        return HttpResponse::HttpOk200(["echo" => $request->message]);
    }
}

class EchoRequest {
    public string $message;
}
```

### Step 3: Register in index.php

```php
<?php
require_once "Micron2/Micron2.php";
require_once "App/Controllers/HelloController.php";

$appBuilder = new WebApplicationBuilder();
$appBuilder->AddConfigurations($_SERVER['DOCUMENT_ROOT']);
$appBuilder->AddCors(new CorsHandlerSettings());

$app = $appBuilder->Build();
$app->AddEndpoints();
$app->Start();
```

### Step 4: Test the Endpoints

```bash
# Test GET
curl http://localhost/EclipseIDEWorks/Micron2/api/hello

# Test POST
curl -X POST http://localhost/EclipseIDEWorks/Micron2/api/echo \
  -H "Content-Type: application/json" \
  -d '{"message": "Test message"}'
```

---

## Configuration

Configuration files are loaded from JSON files in the configured directory. They are merged into the `AppConfiguration` object:

**config.json**:
```json
{
    "Db": {
        "Host": "localhost",
        "Port": "3306",
        "Username": "root",
        "Password": "root"
    }
}
```

Access in your code:
```php
$host = $config->configuration['Db']['Host'];
$port = $config->configuration['Db']['Port'];
```

---

## Error Handling

The framework catches all middleware chain errors and returns HTTP 500:

```php
// In middleware chain, if any middleware returns null
// A 500 Internal Server Error is returned with message:
// "Middleware chain ended with null"
```

Always return either `HttpResponse` or properly chain with `$this->next()`.

---

## Tips & Best Practices

1. **Always chain middleware**: Call `$this->next($context)` unless intentionally stopping the chain
2. **Order matters**: Register middlewares in the order they should execute
3. **Type hints**: Always use type hints in controller methods for proper binding
4. **Scoped services**: Use `AddScoped()` for database connections and services that need per-request lifecycle
5. **Configuration**: Load all settings from JSON files, not hardcoded values
6. **Error responses**: Use the appropriate HTTP status codes (400, 401, 403, 404, 500, etc.)
7. **DTOs**: Create Data Transfer Objects for complex request bodies
8. **Validation**: Validate input before processing in controllers or services

---

## Troubleshooting

| Issue | Solution |
|-------|----------|
| Route not found (404) | Check controller prefix + method path matches exactly |
| Dependency not resolved | Verify dependency is registered with `AddScoped()` before being used |
| Parameter binding fails | Check parameter has correct attribute (`@FromPath`, `@FromQuery`, etc.) |
| Middleware not executing | Ensure middleware is added before `Start()` and extends `AMiddleware` |
| Response is null | Middleware chain ended without returning `HttpResponse` |
| Configuration not loaded | Verify path contains JSON files and `AddConfigurations()` was called |

