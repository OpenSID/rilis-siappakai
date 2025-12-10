# Copilot Coding Guide for Laravel Project (SOLID + Simple)

## Branch Usage
- Always use the **`dev` branch** as the primary reference for code and project structure.
- When creating or updating files, ensure the code aligns with the version in the `dev` branch.
- When providing examples for routes, controllers, or other files, follow the namespaces, folder structure, and dependencies used in the `dev` branch.
- Do not reference code from `main` or other branches unless explicitly instructed.

## Purpose
These are rules for GitHub Copilot when generating code for this Laravel project.  
Always follow Laravel conventions, keep the code simple, clean, and maintainable, applying SOLID principles in a practical way.

---

## General Coding Rules

### 1. Always follow Laravel conventions
- **Routes**: `routes/web.php` for web, `routes/api.php` for API
- **Controllers**: `app/Http/Controllers` - handle HTTP requests/responses only
- **Models**: `app/Models` - Eloquent models for database entities
- **Requests**: `app/Http/Requests` - form validation and authorization
- **Services**: `app/Services` - business logic layer
- **Repositories**: `app/Repositories` with `Contracts` subfolder for interfaces - data access layer
- **Middleware**: `app/Http/Middleware` - request/response filtering
- **Jobs**: `app/Jobs` - queued/background tasks
- **Events**: `app/Events` - application events
- **Listeners**: `app/Listeners` - event handlers

### 2. Use PHP 8.1 features
- **Constructor property promotion**:
  ```php
  public function __construct(
      private readonly UserService $userService,
      private string $apiKey
  ) {}
  ```
- **Type hints and return types**: `string`, `int`, `array`, `?Model`, `void`, `Collection`, etc.
- **Use `readonly` properties** when data shouldn't be modified after construction
- **Nullable types**: `?string`, `?int` instead of mixed types
- **Union types** where appropriate: `string|int`

### 3. Always use dependency injection
- Inject Services, Repositories, and other dependencies through constructors
- Use Laravel's service container and auto-resolution
- Prefer interface type hints over concrete classes in constructors

### 4. Do not mix responsibilities
- **Validation** → Form Requests
- **Business logic** → Services  
- **Data access** → Repositories
- **HTTP handling** → Controllers

---

## SOLID Principles (Simplified)

### **S - Single Responsibility Principle**
- One class = one responsibility
- Controllers only handle HTTP requests/responses
- Services contain business logic
- Repositories handle data persistence
- Models represent database entities

❌ **Wrong**:
```php
public function store(Request $request) {
    // Validation
    $request->validate(['email' => 'required|email']);
    
    // Business logic
    $user = new User();
    $user->email = $request->email;
    if ($user->email === 'admin@example.com') {
        $user->role = 'admin';
    }
    
    // Data persistence
    $user->save();
    
    // Side effects
    Mail::to($user)->send(new WelcomeEmail());
    
    return response()->json($user);
}
```

✅ **Right**:
```php
public function store(CreateUserRequest $request, UserService $userService) {
    $user = $userService->createUser($request->validated());
    return new UserResource($user);
}
```

### **O - Open/Closed Principle**
- Use interfaces and abstract classes for extensibility
- New functionality should extend, not modify existing code

✅ **Example**:
```php
interface PaymentProcessorInterface {
    public function process(Payment $payment): bool;
}

class StripePaymentProcessor implements PaymentProcessorInterface {
    public function process(Payment $payment): bool {
        // Stripe implementation
    }
}
```

### **L - Liskov Substitution Principle**
- Subtypes must be substitutable for their base types
- Implementations should honor the contract defined by interfaces

### **I - Interface Segregation Principle**
- Create specific, focused interfaces
- Don't force classes to depend on methods they don't use

✅ **Example**:
```php
interface UserReaderInterface {
    public function find(int $id): ?User;
    public function findByEmail(string $email): ?User;
}

interface UserWriterInterface {
    public function save(User $user): User;
    public function delete(User $user): bool;
}
```

### **D - Dependency Inversion Principle**
- Depend on abstractions (interfaces), not concretions
- High-level modules shouldn't depend on low-level modules

✅ **Example**:
```php
class UserService {
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly EmailServiceInterface $emailService
    ) {}
}
```

---

## Layer Architecture

### **Controllers** (HTTP Layer)
- Handle HTTP requests and responses only
- Validate input using Form Requests
- Delegate business logic to Services
- Return responses or API resources

```php
class UserController extends Controller {
    public function __construct(
        private readonly UserService $userService
    ) {}

    public function store(CreateUserRequest $request): JsonResponse {
        $user = $this->userService->createUser($request->validated());
        return response()->json(new UserResource($user), 201);
    }
}
```

### **Form Requests** (Validation Layer)
- Handle validation rules and authorization
- Keep validation logic separate from controllers

```php
class CreateUserRequest extends FormRequest {
    public function authorize(): bool {
        return true;
    }

    public function rules(): array {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ];
    }
}
```

### **Services** (Business Logic Layer)
- Contain all business logic and rules
- Coordinate between different repositories
- Handle complex operations and workflows

```php
class UserService {
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly EmailServiceInterface $emailService
    ) {}

    public function createUser(array $data): User {
        $user = $this->userRepository->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $this->emailService->sendWelcomeEmail($user);

        return $user;
    }
}
```

### **Repositories** (Data Access Layer)
- Handle all database operations
- Implement repository interfaces
- Abstract database logic from business logic

```php
interface UserRepositoryInterface {
    public function create(array $data): User;
    public function find(int $id): ?User;
    public function findByEmail(string $email): ?User;
    public function update(User $user, array $data): User;
    public function delete(User $user): bool;
}

class UserRepository implements UserRepositoryInterface {
    public function create(array $data): User {
        return User::create($data);
    }

    public function find(int $id): ?User {
        return User::find($id);
    }
}
```

### **Models** (Data Layer)
- Represent database entities
- Define relationships, mutators, accessors
- Keep business logic in Services, not Models

```php
class User extends Authenticatable {
    protected $fillable = ['name', 'email', 'password'];
    
    protected $hidden = ['password', 'remember_token'];
    
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function posts(): HasMany {
        return $this->hasMany(Post::class);
    }
}
```

---

## Naming Conventions

### **Controllers**
- Singular noun + "Controller": `UserController`, `PostController`
- RESTful methods: `index`, `create`, `store`, `show`, `edit`, `update`, `destroy`

### **Models**
- Singular noun: `User`, `Post`, `Comment`
- Use PascalCase: `BlogPost`, `UserProfile`

### **Services**
- Singular noun + "Service": `UserService`, `PaymentService`
- Business-focused method names: `createUser`, `processPayment`, `sendNotification`

### **Repositories**
- Singular noun + "Repository": `UserRepository`, `PostRepository`
- CRUD method names: `create`, `find`, `update`, `delete`, `findByEmail`

### **Requests**
- Action + Model + "Request": `CreateUserRequest`, `UpdatePostRequest`

### **Resources**
- Model + "Resource": `UserResource`, `PostResource`

---

## Code Quality Guidelines

### **Error Handling**
- Use specific exceptions, not generic ones
- Create custom exceptions when needed
- Handle errors at the appropriate layer

```php
class UserService {
    public function findUser(int $id): User {
        $user = $this->userRepository->find($id);
        
        if (!$user) {
            throw new UserNotFoundException("User with ID {$id} not found");
        }
        
        return $user;
    }
}
```

### **Database Queries**
- Use Repository pattern for database access
- Avoid N+1 queries with eager loading
- Use query scopes in Models for reusable conditions

```php
class PostRepository implements PostRepositoryInterface {
    public function getPublishedWithAuthor(): Collection {
        return Post::with('user')
            ->published()
            ->latest()
            ->get();
    }
}
```

### **API Resources**
- Use API Resources for consistent JSON responses
- Transform data at the presentation layer, not in Services

```php
class UserResource extends JsonResource {
    public function toArray($request): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
```

### **Configuration and Environment**
- Use `config()` helper for configuration values
- Keep sensitive data in `.env` file
- Use type-safe configuration access

---

## Testing Guidelines

### **Unit Tests**
- Test Services and Repositories in isolation
- Mock dependencies using interfaces
- Test business logic thoroughly

### **Feature Tests**
- Test complete workflows through HTTP requests
- Test controller actions and their responses
- Use factories for test data

---

## Common Anti-Patterns to Avoid

❌ **Don't put business logic in Controllers**:
```php
// Wrong - business logic in controller
public function store(Request $request) {
    if ($request->user()->isAdmin() && $request->priority === 'high') {
        // Complex business logic here
    }
}
```

❌ **Don't use Models as Services**:
```php
// Wrong - business logic in model
class User extends Model {
    public function sendWelcomeEmail() {
        Mail::to($this)->send(new WelcomeEmail());
    }
}
```

❌ **Don't chain repository calls in Controllers**:
```php
// Wrong - multiple repository calls in controller
public function show(int $id) {
    $user = $this->userRepository->find($id);
    $posts = $this->postRepository->findByUserId($id);
    $comments = $this->commentRepository->findByUserId($id);
}
```

✅ **Use Services to orchestrate complex operations**:
```php
// Right - delegate to service
public function show(int $id) {
    $userData = $this->userService->getUserWithRelatedData($id);
    return new UserDetailResource($userData);
}
```

---

## Final Reminders

1. **Keep it simple** - don't over-engineer
2. **Follow Laravel conventions** - use the framework as intended  
3. **Separate concerns** - each layer has its responsibility
4. **Use dependency injection** - make code testable and flexible
5. **Type everything** - use PHP 8+ type hints consistently
6. **Write readable code** - clear method and variable names
7. **Test your code** - especially business logic in Services
