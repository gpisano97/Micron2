# Micron2 Framework - Practical Guide & Examples

Complete walkthroughs and real-world examples for using the Micron2 framework.

---

## Table of Contents

1. [Project Structure](#project-structure)
2. [Basic Setup](#basic-setup)
3. [Building a REST API](#building-a-rest-api)
4. [Working with Databases](#working-with-databases)
5. [Authentication & Authorization](#authentication--authorization)
6. [Error Handling](#error-handling)
7. [Testing](#testing)
8. [Deployment](#deployment)

---

## Project Structure

Recommended folder organization:

```
EclipseIDEWorks/Micron2/
├── Micron2/                    # Framework core
│   └── Core/
│       ├── Classes/
│       ├── Controllers/
│       ├── Middlewares/
│       └── WebApplicationEngine/
├── App/                        # Your application
│   ├── Controllers/
│   │   ├── UserController.php
│   │   ├── ProductController.php
│   │   └── OrderController.php
│   ├── Services/               # Business logic
│   │   ├── UserService.php
│   │   ├── ProductService.php
│   │   └── OrderService.php
│   ├── DTOs/                   # Data Transfer Objects
│   │   ├── CreateUserDTO.php
│   │   ├── UpdateProductDTO.php
│   │   └── OrderResponseDTO.php
│   ├── Middlewares/
│   │   ├── AuthenticationMiddleware.php
│   │   ├── LoggingMiddleware.php
│   │   └── ResponseWrapperMiddleware.php
│   ├── Repositories/           # Database access
│   │   ├── UserRepository.php
│   │   └── ProductRepository.php
│   └── Models/                 # Domain entities
│       ├── User.php
│       ├── Product.php
│       └── Order.php
├── config.json                 # Application configuration
├── index.php                   # Entry point
└── .htaccess                   # URL rewriting
```

---

## Basic Setup

### Create index.php

```php
<?php
// Define base path
define('BASE_PATH', __DIR__);
define('APP_PATH', BASE_PATH . '/App');

// Load framework
require_once 'Micron2/Micron2.php';

// Load application services and controllers
require_once 'App/Services/UserService.php';
require_once 'App/Services/ProductService.php';
require_once 'App/Controllers/UserController.php';
require_once 'App/Controllers/ProductController.php';
require_once 'App/Middlewares/LoggingMiddleware.php';
require_once 'App/Middlewares/AuthenticationMiddleware.php';
require_once 'App/Middlewares/ResponseWrapperMiddleware.php';

// Initialize application
$appBuilder = new WebApplicationBuilder();

// Register services
$appBuilder->AddScoped(UserService::class);
$appBuilder->AddScoped(ProductService::class);

// Load configuration
$appBuilder->AddConfigurations(BASE_PATH);

// Setup CORS
$appBuilder->AddCors(new CorsHandlerSettings());

// Build app
$app = $appBuilder->Build();

// Add business logic endpoints
$app->AddEndpoints();

// Add middleware chain (order matters!)
$app->AddMiddleware(LoggingMiddleware::class);
$app->AddMiddleware(AuthenticationMiddleware::class);
$app->AddMiddleware(ResponseWrapperMiddleware::class);

// Start the application
$app->Start();
```

### Create config.json

```json
{
    "Database": {
        "Host": "localhost",
        "Port": "3306",
        "Database": "micron2_app",
        "Username": "root",
        "Password": "root",
        "Charset": "utf8mb4"
    },
    "Application": {
        "Name": "My Micron2 App",
        "Version": "1.0.0",
        "Debug": true,
        "LogLevel": "info"
    },
    "Auth": {
        "JwtSecret": "your-secret-key-here",
        "TokenExpiry": 3600,
        "RefreshTokenExpiry": 604800
    },
    "Api": {
        "BaseUrl": "http://localhost/EclipseIDEWorks/Micron2",
        "Version": "v1"
    }
}
```

---

## Building a REST API

### Step 1: Create DTOs (Data Transfer Objects)

**App/DTOs/UserDTO.php**:
```php
<?php

class CreateUserDTO {
    public string $name;
    public string $email;
    public string $password;
    public ?string $phone = null;
}

class UpdateUserDTO {
    public ?string $name = null;
    public ?string $email = null;
    public ?string $phone = null;
}

class UserResponseDTO {
    public int $id;
    public string $name;
    public string $email;
    public ?string $phone;
    public string $createdAt;

    public function __construct(array $userData) {
        $this->id = $userData['id'];
        $this->name = $userData['name'];
        $this->email = $userData['email'];
        $this->phone = $userData['phone'] ?? null;
        $this->createdAt = $userData['created_at'];
    }
}
```

### Step 2: Create a Service

**App/Services/UserService.php**:
```php
<?php
require_once APP_PATH . '/DTOs/UserDTO.php';

class UserService {
    private HttpContext $_context;
    private AppConfiguration $_config;
    private UserRepository $_repository;

    public function __construct(
        HttpContext $context,
        AppConfiguration $config,
        UserRepository $repository
    ) {
        $this->_context = $context;
        $this->_config = $config;
        $this->_repository = $repository;
    }

    public function getAllUsers(int $page = 1, int $limit = 20): array {
        return $this->_repository->paginate($page, $limit);
    }

    public function getUserById(int $id): ?UserResponseDTO {
        $user = $this->_repository->findById($id);
        
        if (!$user) {
            return null;
        }
        
        return new UserResponseDTO($user);
    }

    public function createUser(CreateUserDTO $dto): UserResponseDTO {
        // Validate input
        $this->validateCreateUser($dto);
        
        // Hash password
        $hashedPassword = password_hash($dto->password, PASSWORD_BCRYPT);
        
        // Save to database
        $userId = $this->_repository->create([
            'name' => $dto->name,
            'email' => $dto->email,
            'password' => $hashedPassword,
            'phone' => $dto->phone,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        // Return created user
        $user = $this->_repository->findById($userId);
        return new UserResponseDTO($user);
    }

    public function updateUser(int $id, UpdateUserDTO $dto): ?UserResponseDTO {
        $user = $this->_repository->findById($id);
        
        if (!$user) {
            return null;
        }
        
        // Update only provided fields
        $updates = [];
        if ($dto->name !== null) $updates['name'] = $dto->name;
        if ($dto->email !== null) $updates['email'] = $dto->email;
        if ($dto->phone !== null) $updates['phone'] = $dto->phone;
        
        $this->_repository->update($id, $updates);
        
        $updatedUser = $this->_repository->findById($id);
        return new UserResponseDTO($updatedUser);
    }

    public function deleteUser(int $id): bool {
        return $this->_repository->delete($id);
    }

    private function validateCreateUser(CreateUserDTO $dto): void {
        if (empty($dto->name)) {
            throw new InvalidArgumentException('Name is required');
        }
        
        if (!filter_var($dto->email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email format');
        }
        
        if (strlen($dto->password) < 8) {
            throw new InvalidArgumentException('Password must be at least 8 characters');
        }
        
        // Check email uniqueness
        if ($this->_repository->findByEmail($dto->email)) {
            throw new InvalidArgumentException('Email already exists');
        }
    }
}
```

### Step 3: Create a Controller

**App/Controllers/UserController.php**:
```php
<?php
require_once 'Micron2/Micron2.php';
require_once APP_PATH . '/Services/UserService.php';
require_once APP_PATH . '/DTOs/UserDTO.php';

#[Controller("api/users")]
class UserController {
    private UserService $_service;
    private HttpContext $_context;

    public function __construct(
        UserService $service,
        HttpContext $context
    ) {
        $this->_service = $service;
        $this->_context = $context;
    }

    // GET /api/users?page=1&limit=20
    #[Get("")]
    public function listUsers(
        #[FromQuery] int $page = 1,
        #[FromQuery] int $limit = 20
    ) {
        try {
            $users = $this->_service->getAllUsers($page, $limit);
            
            return HttpResponse::HttpOk200([
                'users' => $users,
                'page' => $page,
                'limit' => $limit
            ]);
        } catch (Exception $e) {
            return HttpResponse::HttpInternalServerError500("Failed to retrieve users");
        }
    }

    // GET /api/users/{id}
    #[Get("/{id}")]
    public function getUser(#[FromPath] int $id) {
        try {
            $user = $this->_service->getUserById($id);
            
            if (!$user) {
                return HttpResponse::HttpNotFound404("User not found");
            }
            
            return HttpResponse::HttpOk200($user);
        } catch (Exception $e) {
            return HttpResponse::HttpInternalServerError500("Failed to retrieve user");
        }
    }

    // POST /api/users
    #[Post("")]
    public function createUser(#[FromBody] CreateUserDTO $dto) {
        try {
            $user = $this->_service->createUser($dto);
            
            return HttpResponse::HttpCreated201([
                'message' => 'User created successfully',
                'user' => $user
            ]);
        } catch (InvalidArgumentException $e) {
            return HttpResponse::HttpBadRequest400($e->getMessage());
        } catch (Exception $e) {
            return HttpResponse::HttpInternalServerError500("Failed to create user");
        }
    }

    // PUT /api/users/{id}
    #[Put("/{id}")]
    public function updateUser(
        #[FromPath] int $id,
        #[FromBody] UpdateUserDTO $dto
    ) {
        try {
            $user = $this->_service->updateUser($id, $dto);
            
            if (!$user) {
                return HttpResponse::HttpNotFound404("User not found");
            }
            
            return HttpResponse::HttpOk200([
                'message' => 'User updated successfully',
                'user' => $user
            ]);
        } catch (Exception $e) {
            return HttpResponse::HttpInternalServerError500("Failed to update user");
        }
    }

    // DELETE /api/users/{id}
    #[Delete("/{id}")]
    public function deleteUser(#[FromPath] int $id) {
        try {
            $success = $this->_service->deleteUser($id);
            
            if (!$success) {
                return HttpResponse::HttpNotFound404("User not found");
            }
            
            return HttpResponse::HttpNoContent204();
        } catch (Exception $e) {
            return HttpResponse::HttpInternalServerError500("Failed to delete user");
        }
    }
}
```

---

## Working with Databases

### Create a Database Connection

**App/Services/DatabaseConnection.php**:
```php
<?php

class DatabaseConnection {
    private ?PDO $_connection = null;
    private AppConfiguration $_config;

    public function __construct(AppConfiguration $config) {
        $this->_config = $config;
    }

    public function connect(): PDO {
        if ($this->_connection === null) {
            $dbConfig = $this->_config->configuration['Database'];
            
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $dbConfig['Host'],
                $dbConfig['Port'],
                $dbConfig['Database'],
                $dbConfig['Charset']
            );
            
            $this->_connection = new PDO(
                $dsn,
                $dbConfig['Username'],
                $dbConfig['Password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        }
        
        return $this->_connection;
    }

    public function close(): void {
        $this->_connection = null;
    }
}
```

### Create a Repository

**App/Repositories/UserRepository.php**:
```php
<?php

class UserRepository {
    private DatabaseConnection $_db;

    public function __construct(DatabaseConnection $db) {
        $this->_db = $db;
    }

    public function findById(int $id): ?array {
        $pdo = $this->_db->connect();
        
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function findByEmail(string $email): ?array {
        $pdo = $this->_db->connect();
        
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function paginate(int $page = 1, int $limit = 20): array {
        $offset = ($page - 1) * $limit;
        $pdo = $this->_db->connect();
        
        $stmt = $pdo->prepare('SELECT * FROM users LIMIT ? OFFSET ?');
        $stmt->execute([$limit, $offset]);
        
        return $stmt->fetchAll();
    }

    public function create(array $data): int {
        $pdo = $this->_db->connect();
        
        $sql = '
            INSERT INTO users (name, email, password, phone, created_at)
            VALUES (?, ?, ?, ?, ?)
        ';
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $data['name'],
            $data['email'],
            $data['password'],
            $data['phone'],
            $data['created_at']
        ]);
        
        return (int) $pdo->lastInsertId();
    }

    public function update(int $id, array $data): void {
        $pdo = $this->_db->connect();
        
        $setClauses = [];
        $values = [];
        
        foreach ($data as $key => $value) {
            $setClauses[] = "$key = ?";
            $values[] = $value;
        }
        
        $values[] = $id;
        
        $sql = 'UPDATE users SET ' . implode(', ', $setClauses) . ' WHERE id = ?';
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
    }

    public function delete(int $id): bool {
        $pdo = $this->_db->connect();
        
        $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
```

### Register in index.php

```php
require_once APP_PATH . '/Services/DatabaseConnection.php';
require_once APP_PATH . '/Repositories/UserRepository.php';

$appBuilder->AddScoped(DatabaseConnection::class);
$appBuilder->AddScoped(UserRepository::class);
$appBuilder->AddScoped(UserService::class);
```

---

## Authentication & Authorization

### Create Authentication Middleware

**App/Middlewares/AuthenticationMiddleware.php**:
```php
<?php
require_once 'Micron2/Micron2.php';

class AuthenticationMiddleware extends AMiddleware {
    private HttpContext $_context;

    public function __construct(HttpContext $context) {
        $this->_context = $context;
    }

    public function handle(HttpContext $context): HttpContext | HttpResponse | null {
        // Skip auth for public endpoints
        $publicRoutes = [
            '/api/auth/login',
            '/api/auth/register',
            '/api/health'
        ];
        
        if (in_array($context->request->uri, $publicRoutes)) {
            return $this->next($context);
        }
        
        // Get token from Authorization header
        $authHeader = $context->request->headers['Authorization'] ?? null;
        
        if (!$authHeader) {
            return HttpResponse::HttpUnauthorized401("Missing authorization header");
        }
        
        // Extract token
        if (!preg_match('/Bearer\s+(.+)/i', $authHeader, $matches)) {
            return HttpResponse::HttpUnauthorized401("Invalid authorization format");
        }
        
        $token = $matches[1];
        
        // Validate token (implement JWT or session logic)
        if (!$this->validateToken($token)) {
            return HttpResponse::HttpUnauthorized401("Invalid or expired token");
        }
        
        // Continue to next middleware
        return $this->next($context);
    }

    private function validateToken(string $token): bool {
        // Implement JWT validation
        // This is a placeholder
        return !empty($token) && strlen($token) > 10;
    }
}
```

### Create an Authentication Service

**App/Services/AuthenticationService.php**:
```php
<?php

class AuthenticationService {
    private AppConfiguration $_config;

    public function __construct(AppConfiguration $config) {
        $this->_config = config;
    }

    public function generateToken(array $userData): string {
        $header = base64_encode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
        
        $payload = base64_encode(json_encode([
            'sub' => $userData['id'],
            'name' => $userData['name'],
            'email' => $userData['email'],
            'iat' => time(),
            'exp' => time() + $this->_config->configuration['Auth']['TokenExpiry']
        ]));
        
        $signature = base64_encode(
            hash_hmac(
                'sha256',
                "$header.$payload",
                $this->_config->configuration['Auth']['JwtSecret'],
                true
            )
        );
        
        return "$header.$payload.$signature";
    }

    public function verifyToken(string $token): ?array {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            return null;
        }
        
        $header = $parts[0];
        $payload = $parts[1];
        $signature = $parts[2];
        
        // Verify signature
        $expectedSignature = base64_encode(
            hash_hmac(
                'sha256',
                "$header.$payload",
                $this->_config->configuration['Auth']['JwtSecret'],
                true
            )
        );
        
        if ($signature !== $expectedSignature) {
            return null;
        }
        
        // Decode payload
        $decoded = json_decode(base64_decode($payload), true);
        
        // Check expiration
        if ($decoded['exp'] < time()) {
            return null;
        }
        
        return $decoded;
    }
}
```

### Create Authentication Controller

**App/Controllers/AuthController.php**:
```php
<?php

#[Controller("api/auth")]
class AuthController {
    private UserService $_userService;
    private AuthenticationService $_authService;

    public function __construct(
        UserService $userService,
        AuthenticationService $authService
    ) {
        $this->_userService = $userService;
        $this->_authService = $authService;
    }

    #[Post("/login")]
    public function login(#[FromBody] LoginDTO $credentials) {
        try {
            // Find user by email
            $user = $this->_userService->getUserByEmail($credentials->email);
            
            if (!$user) {
                return HttpResponse::HttpUnauthorized401("Invalid credentials");
            }
            
            // Verify password
            if (!password_verify($credentials->password, $user->password)) {
                return HttpResponse::HttpUnauthorized401("Invalid credentials");
            }
            
            // Generate token
            $token = $this->_authService->generateToken([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email
            ]);
            
            return HttpResponse::HttpOk200([
                'token' => $token,
                'user' => $user
            ]);
        } catch (Exception $e) {
            return HttpResponse::HttpInternalServerError500("Login failed");
        }
    }

    #[Post("/register")]
    public function register(#[FromBody] CreateUserDTO $dto) {
        try {
            $user = $this->_userService->createUser($dto);
            
            $token = $this->_authService->generateToken([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email
            ]);
            
            return HttpResponse::HttpCreated201([
                'token' => $token,
                'user' => $user
            ]);
        } catch (InvalidArgumentException $e) {
            return HttpResponse::HttpBadRequest400($e->getMessage());
        } catch (Exception $e) {
            return HttpResponse::HttpInternalServerError500("Registration failed");
        }
    }
}
```

---

## Error Handling

### Create an Error Middleware

**App/Middlewares/ErrorHandlingMiddleware.php**:
```php
<?php

class ErrorHandlingMiddleware extends AMiddleware {
    private HttpContext $_context;
    private AppConfiguration $_config;

    public function __construct(HttpContext $context, AppConfiguration $config) {
        $this->_context = $context;
        $this->_config = $config;
    }

    public function handle(HttpContext $context): HttpContext | HttpResponse | null {
        try {
            return $this->next($context);
        } catch (InvalidArgumentException $e) {
            return $this->handleValidationError($e);
        } catch (UnauthorizedException $e) {
            return $this->handleUnauthorized($e);
        } catch (ForbiddenException $e) {
            return $this->handleForbidden($e);
        } catch (Exception $e) {
            return $this->handleServerError($e);
        }
    }

    private function handleValidationError(Exception $e): HttpResponse {
        return HttpResponse::HttpBadRequest400([
            'error' => 'validation_error',
            'message' => $e->getMessage()
        ]);
    }

    private function handleUnauthorized(Exception $e): HttpResponse {
        return HttpResponse::HttpUnauthorized401([
            'error' => 'unauthorized',
            'message' => $e->getMessage()
        ]);
    }

    private function handleForbidden(Exception $e): HttpResponse {
        return HttpResponse::HttpForbidden403([
            'error' => 'forbidden',
            'message' => $e->getMessage()
        ]);
    }

    private function handleServerError(Exception $e): HttpResponse {
        // Log error
        $this->logError($e);
        
        // Return generic error in production
        if (!$this->_config->configuration['Application']['Debug']) {
            return HttpResponse::HttpInternalServerError500([
                'error' => 'internal_server_error',
                'message' => 'An error occurred processing your request'
            ]);
        }
        
        // Return detailed error in debug mode
        return HttpResponse::HttpInternalServerError500([
            'error' => 'internal_server_error',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
    }

    private function logError(Exception $e): void {
        $logFile = BASE_PATH . '/logs/error.log';
        
        if (!is_dir(dirname($logFile))) {
            mkdir(dirname($logFile), 0755, true);
        }
        
        $message = sprintf(
            "[%s] %s in %s:%d\n%s\n\n",
            date('Y-m-d H:i:s'),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );
        
        file_put_contents($logFile, $message, FILE_APPEND);
    }
}
```

### Custom Exception Classes

**App/Exceptions/UnauthorizedException.php**:
```php
<?php

class UnauthorizedException extends Exception {}
class ForbiddenException extends Exception {}
class NotFoundException extends Exception {}
class ValidationException extends InvalidArgumentException {}
```

---

## Testing

### Create Unit Tests with PHPUnit

**tests/UserServiceTest.php**:
```php
<?php

use PHPUnit\Framework\TestCase;

class UserServiceTest extends TestCase {
    private UserService $service;
    private UserRepository $repository;
    private HttpContext $context;

    protected function setUp(): void {
        $config = $this->createMock(AppConfiguration::class);
        $this->repository = $this->createMock(UserRepository::class);
        $this->context = new HttpContext();
        
        $this->service = new UserService($this->context, $config, $this->repository);
    }

    public function testGetUserByIdReturnsNull() {
        $this->repository
            ->method('findById')
            ->willReturn(null);
        
        $result = $this->service->getUserById(999);
        
        $this->assertNull($result);
    }

    public function testGetUserByIdReturnsUser() {
        $userData = [
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => null,
            'created_at' => '2024-01-01 12:00:00'
        ];
        
        $this->repository
            ->method('findById')
            ->willReturn($userData);
        
        $result = $this->service->getUserById(1);
        
        $this->assertNotNull($result);
        $this->assertEquals('John Doe', $result->name);
    }

    public function testCreateUserValidatesEmail() {
        $this->expectException(InvalidArgumentException::class);
        
        $dto = new CreateUserDTO();
        $dto->name = 'John Doe';
        $dto->email = 'invalid-email';
        $dto->password = 'password123';
        
        $this->service->createUser($dto);
    }

    public function testCreateUserValidatesPassword() {
        $this->expectException(InvalidArgumentException::class);
        
        $dto = new CreateUserDTO();
        $dto->name = 'John Doe';
        $dto->email = 'john@example.com';
        $dto->password = 'short';
        
        $this->service->createUser($dto);
    }
}
```

### Integration Tests

**tests/UserControllerTest.php**:
```php
<?php

class UserControllerTest extends TestCase {
    private UserController $controller;
    private UserService $service;
    private HttpContext $context;

    protected function setUp(): void {
        $this->context = new HttpContext();
        $this->service = $this->createMock(UserService::class);
        $this->controller = new UserController($this->service, $this->context);
    }

    public function testGetUserReturnsNotFound() {
        $this->service
            ->method('getUserById')
            ->willReturn(null);
        
        $response = $this->controller->getUser(999);
        
        $this->assertEquals(404, $response->statusCode);
    }

    public function testGetUserReturnsOk() {
        $userDTO = new UserResponseDTO([
            'id' => 1,
            'name' => 'John',
            'email' => 'john@example.com',
            'phone' => null,
            'created_at' => '2024-01-01 12:00:00'
        ]);
        
        $this->service
            ->method('getUserById')
            ->willReturn($userDTO);
        
        $response = $this->controller->getUser(1);
        
        $this->assertEquals(200, $response->statusCode);
    }
}
```

---

## Deployment

### Production Checklist

- [ ] Set `Debug: false` in config.json
- [ ] Use strong database passwords
- [ ] Enable HTTPS (.htaccess with SSL enforcement)
- [ ] Set appropriate file permissions
- [ ] Configure error logging
- [ ] Set up cron jobs for maintenance
- [ ] Use environment variables for sensitive data
- [ ] Implement rate limiting middleware
- [ ] Enable CORS only for trusted domains
- [ ] Keep framework and dependencies updated

### Apache .htaccess Configuration

```apache
# Force HTTPS
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Remove trailing slashes
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)/$ /$1 [R=301,L]

# Route to index.php
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?uri=$1 [QSA,L]

# Security headers
Header set X-Content-Type-Options "nosniff"
Header set X-Frame-Options "SAMEORIGIN"
Header set Strict-Transport-Security "max-age=31536000; includeSubDomains"
```

### Environment Variables

Create `.env` file:
```
DB_HOST=localhost
DB_PORT=3306
DB_NAME=micron2_app
DB_USER=root
DB_PASS=strongpassword
JWT_SECRET=your-secret-key
APP_DEBUG=false
```

Load in PHP:
```php
$env = parse_ini_file('.env');

$config = [
    'Database' => [
        'Host' => $env['DB_HOST'],
        'Port' => $env['DB_PORT'],
        'Database' => $env['DB_NAME'],
        'Username' => $env['DB_USER'],
        'Password' => $env['DB_PASS']
    ]
];
```

---

## Performance Tips

1. **Enable caching** in Apache `.htaccess`
2. **Use indexes** on frequently queried database columns
3. **Implement pagination** for large result sets
4. **Cache repeated queries** in services
5. **Use lazy loading** for related data
6. **Implement request compression** in middleware
7. **Optimize database queries** with proper joins
8. **Monitor log files** for performance issues

