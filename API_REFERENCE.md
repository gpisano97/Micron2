# Micron2 Framework - API Reference

Quick lookup guide for all classes and methods.

## Core Web Application

### WebApplicationBuilder

```php
class WebApplicationBuilder {
    public function __construct()
    public function AddScoped(string $className): void
    public function AddScopedWithoutDI(callable $objectConstructor): void
    public function AddConfigurations(string $path): void
    public function AddCors(CorsHandlerSettings $settings): void
    public function AddDbContext(): void
    public function Build(): WebApplication
}
```

### WebApplication

```php
final class WebApplication {
    public function __construct(DependencyRegister $register, HttpContext $context)
    public function AddMiddleware(string $middlewareClassName): void
    public function AddEndpoints(): void
    public function Start(): void
}
```

---

## HTTP Classes

### HttpContext

```php
final class HttpContext {
    public HttpRequest $request
    public HttpResponse $response
    public function __construct()
}
```

### HttpRequest

```php
final class HttpRequest {
    public string $uri
    public HttpMethod $method
    public string $requestBody
    public array $headers
    public array $post
    public array $files
    
    public function __construct()
    public function GetRequestCreatedAt(): DateTime
}
```

### HttpResponse

```php
final class HttpResponse {
    public int $statusCode
    public mixed $content
    public array $headers = []
    
    public function __construct(
        int $statusCode,
        mixed $content,
        ?HttpContentType $contentType = null
    )
    public function addHeader(HttpHeader $header, string $value): self
    
    // 1xx Informational
    public static function HttpContinue100(...): HttpResponse
    public static function HttpSwitchingProtocols101(...): HttpResponse
    
    // 2xx Success
    public static function HttpOk200(...): HttpResponse
    public static function HttpCreated201(...): HttpResponse
    public static function HttpAccepted202(...): HttpResponse
    public static function HttpNoContent204(...): HttpResponse
    
    // 3xx Redirection
    public static function HttpMovedPermanently301(...): HttpResponse
    public static function HttpFound302(...): HttpResponse
    public static function HttpSeeOther303(...): HttpResponse
    public static function HttpNotModified304(...): HttpResponse
    public static function HttpTemporaryRedirect307(...): HttpResponse
    
    // 4xx Client Error
    public static function HttpBadRequest400(...): HttpResponse
    public static function HttpUnauthorized401(...): HttpResponse
    public static function HttpForbidden403(...): HttpResponse
    public static function HttpNotFound404(...): HttpResponse
    public static function HttpMethodNotAllowed405(...): HttpResponse
    public static function HttpConflict409(...): HttpResponse
    public static function HttpTooManyRequests429(...): HttpResponse
    
    // 5xx Server Error
    public static function HttpInternalServerError500(...): HttpResponse
    public static function HttpNotImplemented501(...): HttpResponse
    public static function HttpBadGateway502(...): HttpResponse
    public static function HttpServiceUnavailable503(...): HttpResponse
    public static function HttpGatewayTimeout504(...): HttpResponse
}
```

### AppConfiguration

```php
final class AppConfiguration {
    public array $configuration = []
    
    public function __construct(string $configPath)
}
```

---

## Dependency Injection

### DependencyRegister

```php
final class DependencyRegister {
    public array $_createdScopedDependency
    public array $_toCreateScopedDependency
    public array $_transientDependency
    
    public function __construct()
}
```

### DependencyResolver

```php
final class DependencyResolver {
    public static function ResolveScoped(DependencyRegister $register): void
    public static function ResolveExternal(DependencyRegister $register, string $className): object
}
```

---

## Controllers & Routing

### Controller Attribute

```php
#[Attribute(Attribute::TARGET_CLASS)]
final class Controller {
    public function __construct(string $prefix = "")
    public function GetPrefix(): string
}

// Usage:
#[Controller("api/users")]
class UserController { }
```

### Route Attributes

```php
abstract class RouteMethod {
    public function __construct(string $path = "", HttpMethod|null $method = null)
    public function GetPath(): string
    public function GetMethod(): ?HttpMethod
}

#[Attribute(Attribute::TARGET_METHOD)]
final class Get extends RouteMethod

#[Attribute(Attribute::TARGET_METHOD)]
final class Post extends RouteMethod

#[Attribute(Attribute::TARGET_METHOD)]
final class Put extends RouteMethod

#[Attribute(Attribute::TARGET_METHOD)]
final class Delete extends RouteMethod

#[Attribute(Attribute::TARGET_METHOD)]
final class Patch extends RouteMethod
```

### Parameter Binding Attributes

```php
#[Attribute(Attribute::TARGET_PARAMETER)]
class FromPath { }

#[Attribute(Attribute::TARGET_PARAMETER)]
class FromQuery { }

#[Attribute(Attribute::TARGET_PARAMETER)]
class FromBody { }

#[Attribute(Attribute::TARGET_PARAMETER)]
class FromFormData { }
```

### Route Metadata Classes

```php
final class ParameterMetadata {
    public function __construct(
        public string $name,
        public ?string $type,
        public ?string $source, // 'path', 'query', 'body', 'form'
        public bool $hasDefault,
        public mixed $defaultValue
    ) { }
}

final class RouteMetadata {
    public function __construct(
        public string $className,
        public string $methodName,
        public HttpMethod $httpMethod,
        public string $path,
        public string $prefix,
        public array $parameters = []
    ) { }
}

class RouteMap {
    public function add(string $key, RouteMetadata $meta): void
    public function all(): array
    public function get(string $key): ?RouteMetadata
}
```

### Route Discovery & Matching

```php
final class ControllerDiscovery {
    public static function scan(): RouteMap
}

final class MatchedRoute {
    public function __construct(
        public RouteMetadata $metadata,
        public array $uriParams = [],
        public array $queryParams = []
    ) { }
}

final class RouteMatcher {
    public static function match(
        HttpContext $context,
        RouteMap $map
    ): ?MatchedRoute
}
```

### Controller Invocation

```php
final class ControllerInvoker {
    public static function invoke(
        DependencyRegister $register,
        MatchedRoute $matchedRouteData,
        HttpContext $context
    ): array|object|string|int|float
}
```

### Parameter Binding

```php
final class ParameterBinder {
    public static function bind(
        MatchedRoute $matched,
        HttpContext $context
    ): array
}
```

---

## Middleware

### Middleware Interface

```php
interface IMiddleware {
    function setNext(IMiddleware $nextMiddleware): IMiddleware
    function handle(HttpContext $context): HttpContext | HttpResponse | null
    function next(HttpContext $context): HttpContext | HttpResponse | null
}

abstract class AMiddleware implements IMiddleware {
    private ?IMiddleware $_nextHandler = null
    
    public function setNext(IMiddleware $nextMiddleware): IMiddleware
    public function handle(HttpContext $context): HttpContext | HttpResponse | null
    function next(HttpContext $context): HttpContext | HttpResponse | null
}
```

---

## Enums

### HttpContentType

```php
enum HttpContentType: string {
    case TEXT_PLAIN = 'text/plain'
    case TEXT_HTML = 'text/html'
    case TEXT_CSS = 'text/css'
    case TEXT_CSV = 'text/csv'
    case TEXT_XML = 'text/xml'
    case APPLICATION_JSON = 'application/json'
    case APPLICATION_JAVASCRIPT = 'application/javascript'
    case FORM_URLENCODED = 'application/x-www-form-urlencoded'
    case FORM_DATA = 'multipart/form-data'
    case OCTET_STREAM = 'application/octet-stream'
    case PDF = 'application/pdf'
    case ZIP = 'application/zip'
    case IMAGE_PNG = 'image/png'
    case IMAGE_JPEG = 'image/jpeg'
    case IMAGE_GIF = 'image/gif'
    case IMAGE_SVG = 'image/svg+xml'
    case IMAGE_WEBP = 'image/webp'
    case AUDIO_MPEG = 'audio/mpeg'
    case VIDEO_MP4 = 'video/mp4'
    case VIDEO_WEBM = 'video/webm'
    case APPLICATION_XML = 'application/xml'
    case APPLICATION_SOAP_XML = 'application/soap+xml'
    case FONT_WOFF = 'font/woff'
    case FONT_WOFF2 = 'font/woff2'
}
```

### HttpMethod

```php
enum HttpMethod: string {
    case GET = 'GET'
    case POST = 'POST'
    case PUT = 'PUT'
    case DELETE = 'DELETE'
    case PATCH = 'PATCH'
    case HEAD = 'HEAD'
    case OPTIONS = 'OPTIONS'
    case TRACE = 'TRACE'
    case CONNECT = 'CONNECT'
}
```

### HttpHeader

```php
enum HttpHeader: string {
    // General Headers
    case ACCEPT = 'Accept'
    case ACCEPT_ENCODING = 'Accept-Encoding'
    case ACCEPT_LANGUAGE = 'Accept-Language'
    case CACHE_CONTROL = 'Cache-Control'
    case CONNECTION = 'Connection'
    case CONTENT_LENGTH = 'Content-Length'
    case CONTENT_DISPOSITION = 'Content-Disposition'
    case CONTENT_TYPE = 'Content-Type'
    case DATE = 'Date'
    case HOST = 'Host'
    case PRAGMA = 'Pragma'
    case TRAILER = 'Trailer'
    case TRANSFER_ENCODING = 'Transfer-Encoding'
    case UPGRADE = 'Upgrade'
    case VIA = 'Via'
    
    // Authentication
    case AUTHORIZATION = 'Authorization'
    case WWW_AUTHENTICATE = 'WWW-Authenticate'
    case PROXY_AUTHENTICATE = 'Proxy-Authenticate'
    case PROXY_AUTHORIZATION = 'Proxy-Authorization'
    
    // CORS & Security
    case ORIGIN = 'Origin'
    case ACCESS_CONTROL_ALLOW_ORIGIN = 'Access-Control-Allow-Origin'
    case ACCESS_CONTROL_ALLOW_METHODS = 'Access-Control-Allow-Methods'
    case ACCESS_CONTROL_ALLOW_HEADERS = 'Access-Control-Allow-Headers'
    case ACCESS_CONTROL_REQUEST_METHOD = 'Access-Control-Request-Method'
    case ACCESS_CONTROL_REQUEST_HEADERS = 'Access-Control-Request-Headers'
    case ACCESS_CONTROL_MAX_AGE = 'Access-Control-Max-Age'
    case STRICT_TRANSPORT_SECURITY = 'Strict-Transport-Security'
    case X_FRAME_OPTIONS = 'X-Frame-Options'
    case X_CONTENT_TYPE_OPTIONS = 'X-Content-Type-Options'
    case CONTENT_SECURITY_POLICY = 'Content-Security-Policy'
    
    // Client/Server Info
    case USER_AGENT = 'User-Agent'
    case REFERER = 'Referer'
    case SERVER = 'Server'
}
```

---

## Common Patterns

### Create a Service

```php
class UserService {
    public function __construct(
        private HttpContext $context,
        private AppConfiguration $config
    ) { }
    
    public function getUser(int $id) { }
}
```

### Register and Use Service

```php
$appBuilder->AddScoped(UserService::class);

#[Controller("api/users")]
class UserController {
    public function __construct(
        private UserService $service,
        private HttpContext $context
    ) { }
    
    #[Get("/{id}")]
    public function getUser(#[FromPath] int $id) {
        return HttpResponse::HttpOk200($this->service->getUser($id));
    }
}
```

### Create a DTO

```php
class CreateUserDTO {
    public string $name;
    public string $email;
    public string $password;
}

#[Controller("api/users")]
class UserController {
    #[Post("/")]
    public function create(#[FromBody] CreateUserDTO $dto) {
        // $dto->name, $dto->email, $dto->password available
        return HttpResponse::HttpCreated201($newUser);
    }
}
```

### Create Custom Middleware

```php
class MyMiddleware extends AMiddleware {
    public function __construct(private AppConfiguration $config) { }
    
    public function handle(HttpContext $context): HttpContext | HttpResponse | null {
        // Process request
        $context->request->headers['X-Custom'] = 'value';
        
        // Pass to next
        $response = $this->next($context);
        
        // Process response
        if ($response instanceof HttpResponse) {
            $response->addHeader(HttpHeader::CACHE_CONTROL, 'max-age=3600');
        }
        
        return $response;
    }
}
```

### Return Different Response Types

```php
// JSON
return HttpResponse::HttpOk200($data, HttpContentType::APPLICATION_JSON);

// HTML
return HttpResponse::HttpOk200("<h1>Hello</h1>", HttpContentType::TEXT_HTML);

// Plain text
return HttpResponse::HttpOk200("Success", HttpContentType::TEXT_PLAIN);

// With headers
$response = HttpResponse::HttpOk200($data);
$response->addHeader(HttpHeader::CACHE_CONTROL, 'no-cache');
return $response;

// Error responses
return HttpResponse::HttpNotFound404("Resource not found");
return HttpResponse::HttpBadRequest400("Invalid input");
return HttpResponse::HttpUnauthorized401("Not authenticated");
return HttpResponse::HttpForbidden403("Not authorized");
return HttpResponse::HttpConflict409("Resource already exists");
return HttpResponse::HttpInternalServerError500("Server error");
```

### Access Configuration

```php
$config = new AppConfiguration($_SERVER['DOCUMENT_ROOT']);
$dbHost = $config->configuration['Db']['Host'];
$dbPort = $config->configuration['Db']['Port'];
$dbUsername = $config->configuration['Db']['Username'];
$dbPassword = $config->configuration['Db']['Password'];
```

### Handle Different Parameter Sources

```php
#[Controller("api/search")]
class SearchController {
    // Path parameters
    #[Get("/user/{userId}")]
    public function getUserPosts(#[FromPath] int $userId) { }
    
    // Query parameters
    #[Get("/posts")]
    public function searchPosts(#[FromQuery] string $q, #[FromQuery] int $limit = 10) { }
    
    // Request body (JSON)
    #[Post("/filter")]
    public function filterUsers(#[FromBody] FilterDTO $filters) { }
    
    // Form data
    #[Post("/upload")]
    public function uploadFile(#[FromFormData] UploadDTO $file) { }
    
    // Multiple sources in one method
    #[Put("/{id}")]
    public function update(
        #[FromPath] int $id,
        #[FromBody] UpdateDTO $data,
        #[FromQuery] string $notify = "false"
    ) { }
}
```

---

## Initialization Checklist

- [ ] Create `WebApplicationBuilder` instance
- [ ] Register services with `AddScoped()` 
- [ ] Load configuration with `AddConfigurations()`
- [ ] Setup CORS with `AddCors()`
- [ ] Build application with `Build()`
- [ ] Add endpoints with `AddEndpoints()`
- [ ] Add custom middleware with `AddMiddleware()`
- [ ] Start application with `Start()`

---

## Status Code Quick Reference

| Code | Method | Description |
|------|--------|-------------|
| 200 | HttpOk200 | Success |
| 201 | HttpCreated201 | Created |
| 204 | HttpNoContent204 | No content |
| 400 | HttpBadRequest400 | Bad request |
| 401 | HttpUnauthorized401 | Unauthorized |
| 403 | HttpForbidden403 | Forbidden |
| 404 | HttpNotFound404 | Not found |
| 405 | HttpMethodNotAllowed405 | Method not allowed |
| 409 | HttpConflict409 | Conflict |
| 429 | HttpTooManyRequests429 | Too many requests |
| 500 | HttpInternalServerError500 | Internal error |
| 501 | HttpNotImplemented501 | Not implemented |
| 503 | HttpServiceUnavailable503 | Service unavailable |

