# NutriNova - MVC Architecture Refactoring Guide

## Project Overview
NutriNova has been successfully refactored from a monolithic PHP structure into a clean **Model-View-Controller (MVC)** architecture. This document explains the new structure, organization, and how to maintain/extend the project.

## New Project Structure

```
Integration_FINAL_NEW_KN/
├── models/                          # Business Logic & Database Operations
│   ├── Database.php                 # Database connection class (foundational)
│   ├── User.php                     # User registration, login, profile operations
│   ├── Post.php                     # Post CRUD operations (create, read, update, delete)
│   └── Cart.php                     # Shopping cart operations
│
├── controllers/                     # Request Handling & Application Logic
│   ├── UserController.php           # Routes user requests to User model
│   ├── PostController.php           # Routes post requests to Post model
│   └── CartController.php           # Routes cart requests to Cart model
│
├── views/                           # User Interface (HTML, CSS, JS)
│   ├── index.html                   # Main HTML template
│   └── (CSS and JS in root)
│
├── index.php                        # MAIN ROUTER - Central entry point for all requests
├── nutrinova_Final_Website.html     # Original HTML file (kept for reference)
├── styles.css                       # Styling for all pages
├── script.js                        # Frontend JavaScript (updated with new paths)
├── db_connect.php                   # Legacy compatibility file
│
└── [Other Files]                    # Legacy files (kept for backward compatibility)
    ├── register_user.php
    ├── login_check.php
    ├── save_post.php
    ├── get_posts.php
    ├── update_post.php
    ├── delete_post.php
    ├── add_to_cart.php
    └── ... (other legacy files)
```

## Architecture Explanation

### 1. **Models** (`/models`)
Models handle all business logic and database operations. They are the core of the application.

#### `Database.php`
- **Purpose**: Manages database connection and initialization
- **Key Methods**:
  - `connect()`: Establishes MySQLi connection
  - `getConnection()`: Returns active connection
  - `escapeString()`: Prevents SQL injection
  - `closeConnection()`: Properly closes connection
- **Usage**: All other models extend from this

#### `User.php`
- **Purpose**: Manages user operations
- **Key Methods**:
  - `register(array $data)`: Creates new user account
  - `login(string $email, string $password)`: Authenticates user
  - `getUserById(int $id)`: Retrieves user profile
  - `getUserByEmail(string $email)`: Finds user by email
- **Database Table**: `users` (id, Nom, Prenom, Email, Mot_de_passe, Taille_cm, Poids_kg, Objectif, Niveau_sportif, created_at)

#### `Post.php`
- **Purpose**: Manages community forum posts
- **Key Methods**:
  - `create(array $data)`: Creates new post
  - `getAll(int $limit)`: Retrieves all posts with pagination
  - `getById(int $id)`: Gets specific post
  - `update(int $id, array $data)`: Updates post content
  - `delete(int $id)`: Removes post
- **Database Table**: `post` (id, nom_auteur, titre_post, contenu_post, created_at)
- **Validations**: Title max 255 chars, Content max 10000 chars

#### `Cart.php`
- **Purpose**: Manages shopping cart
- **Key Methods**:
  - `addProduct(array $data)`: Adds item to cart
  - `getAll()`: Retrieves all cart items
  - `removeProduct(int $id)`: Removes item by ID
  - `removeByNameAndPrice(string $nom, float $prix)`: Removes by name/price
  - `clear()`: Empties entire cart
- **Database Table**: `panier` (id, Nom, Prix, created_at)

### 2. **Controllers** (`/controllers`)
Controllers handle HTTP requests and act as intermediaries between Views and Models.

#### `UserController.php`
- **Routes** (via `?action=`):
  - `register`: Handles POST registration
  - `login`: Handles POST login
  - `profile_[id]`: Retrieves user profile
- **Flow**: Receives request → Calls User model → Returns JSON response
- **Error Handling**: Returns structured JSON with success/error fields

#### `PostController.php`
- **Routes**:
  - `create_post`: Creates new post (requires login)
  - `get_all_posts`: Retrieves all posts
  - `get_post_[id]`: Gets specific post
  - `update_post_[id]`: Updates post (POST request)
  - `delete_post_[id]`: Deletes post (POST request)
- **Flow**: Validates input → Calls Post model → Returns JSON
- **Built-in Validations**: Field length checks, required field validation

#### `CartController.php`
- **Routes**:
  - `add_to_cart`: Adds product to cart
  - `get_cart`: Retrieves all cart items
  - `remove_from_cart`: Removes product
  - `clear_cart`: Empties cart
- **Dual Support**: Accepts both ID-based and name/price-based lookups

### 3. **Views** (`/views` and root)
Views contain all presentation logic (HTML, CSS, JavaScript).

- **Main File**: `nutrinova_Final_Website.html` (or `views/index.html`)
- **Styling**: `styles.css` - Complete responsive design
- **Interactivity**: `script.js` - Frontend logic and API calls

### 4. **Router** (`index.php`)
Central entry point that routes all requests to appropriate controllers.

```php
// Example routing logic
if (strpos($action, 'register') === 0) {
    require_once CONTROLLERS_PATH . 'UserController.php';
} elseif (strpos($action, 'post') !== false) {
    require_once CONTROLLERS_PATH . 'PostController.php';
} else {
    // Serve view
    require_once VIEWS_PATH . 'index.html';
}
```

## How Requests Flow Through the MVC

### Example: User Registration
1. **User submits form** in view (`nutrinova_Final_Website.html`)
2. **JavaScript intercepts** (script.js) →  `fetch('index.php?action=register', {...})`
3. **Router** (index.php) receives request, loads `UserController.php`
4. **Controller** validates input, calls `User::register()` from model
5. **Model** executes database operations, returns structured response
6. **Controller** returns JSON to client
7. **Client-side JavaScript** handles response, updates UI

## API Endpoints (New MVC Structure)

### User Endpoints
```
POST /index.php?action=register
  Parameters: nom, prenom, email, mot_de_passe, taille, poids, objectif, niveau_sportif
  Response: { success: bool, message: string, id: int }

POST /index.php?action=login
  Parameters: email, mot_de_passe
  Response: { success: bool, user: {...}, message: string }
```

### Post Endpoints
```
POST /index.php?action=create_post
  Parameters: nom_auteur, titre_post, contenu_post
  Response: { success: bool, id: int, message: string }

GET /index.php?action=get_all_posts
  Response: { success: bool, posts: [...] }

GET /index.php?action=get_post_[id]
  Response: { success: bool, post: {...} }

POST /index.php?action=update_post_[id]
  Parameters: titre_post, contenu_post
  Response: { success: bool, message: string }

POST /index.php?action=delete_post_[id]
  Response: { success: bool, message: string }
```

### Cart Endpoints
```
POST /index.php?action=add_to_cart
  Parameters: nom, prix
  Response: { success: bool, id: int, message: string }

GET /index.php?action=get_cart
  Response: [{ id, Nom, Prix, created_at }, ...]

POST /index.php?action=remove_from_cart
  Parameters: id (or nom and prix)
  Response: { success: bool, message: string }

POST /index.php?action=clear_cart
  Response: { success: bool, message: string }
```

## Key Improvements

### 1. **Separation of Concerns**
- ✅ Models handle ONLY data operations
- ✅ Controllers handle ONLY request logic
- ✅ Views contain ONLY presentation

### 2. **Code Reusability**
- Instead of duplicated SQL in multiple files, logic is in one model
- Controllers can reuse models for different endpoints
- Easier to maintain and update

### 3. **Error Handling**
- Consistent JSON response format
- Proper HTTP headers set by controllers
- Try-catch error handling throughout

### 4. **Security**
- All inputs escaped via `Database::escapeString()`
- Input validation in models
- Clean separation reduces attack surface

### 5. **Scalability**
- Easy to add new models (e.g., `Notification.php`)
- Easy to add new controllers (e.g., `NotificationController.php`)
- Router automatically handles new actions

### 6. **Testing & Debugging**
- Each layer can be tested independently
- Clear request/response flow
- Easier to trace issues

## Migration Guide (Old → New)

### Old Code Issues (Fixed)
| Old Path | New Path | Controller |
|----------|----------|-----------|
| `register_user.php` | `index.php?action=register` | UserController |
| `login_check.php` | `index.php?action=login` | UserController |
| `save_post.php` | `index.php?action=create_post` | PostController |
| `get_posts.php` | `index.php?action=get_all_posts` | PostController |
| `update_post.php` | `index.php?action=update_post_[id]` | PostController |
| `delete_post.php` | `index.php?action=delete_post_[id]` | PostController |
| `add_to_cart.php` | `index.php?action=add_to_cart` | CartController |
| `get_cart.php` | `index.php?action=get_cart` | CartController |
| `delete_from_db.php` | `index.php?action=remove_from_cart` | CartController |

### JavaScript Updates
All `fetch()` calls in `script.js` have been updated to use new MVC routes:

```javascript
// Old: fetch('register_user.php', { ... })
// New:
fetch('index.php?action=register', { ... })

// Old: fetch('get_posts.php')
// New:
fetch('index.php?action=get_all_posts')
```

## How to Extend the Application

### Adding a New Feature (Example: Recipes)

1. **Create Model** (`models/Recipe.php`):
```php
class Recipe {
    // Database operations for recipes
    public function getAll() { ... }
    public function create($data) { ... }
}
```

2. **Create Controller** (`controllers/RecipeController.php`):
```php
class RecipeController {
    private $recipeModel;
    
    public function __construct() {
        require_once __DIR__ . '/../models/Recipe.php';
        $this->recipeModel = new Recipe();
    }
    
    public function getAll() { ... }
}

// Router at bottom
if ($_GET['action'] === 'get_recipes') {
    $controller = new RecipeController();
    $controller->getAll();
}
```

3. **Update Router** (`index.php`):
```php
elseif (strpos($action, 'recipe') !== false) {
    require_once CONTROLLERS_PATH . 'RecipeController.php';
}
```

4. **Call from Views** (script.js):
```javascript
fetch('index.php?action=get_recipes')
    .then(r => r.json())
    .then(data => { /* handle response */ })
```

## Database Setup

### Tables Created Automatically
The models automatically create tables on first use:

- `users` - User accounts
- `post` - Community forum posts
- `panier` - Shopping cart items

### Connection Details
- **Server**: localhost
- **Database**: integration_nutrition_ai
- **Username**: root (default XAMPP)
- **Password**: (empty - default XAMPP)
- **Charset**: utf8mb4

## Best Practices

### When Adding New Code:
1. ✅ **Keep models focused** - ONLY database logic
2. ✅ **Use models from controllers** - Don't duplicate logic
3. ✅ **Return JSON from controllers** - Consistent API responses
4. ✅ **Validate input** - Both in controller AND model
5. ✅ **Use prepared statements** eventually - Current uses escaping (consider upgrading to prepared statements for better security)
6. ✅ **Handle errors gracefully** - Return meaningful error messages

### Current Limitations (Future Improvements):
- ⚠️ No prepared statements (uses escaping instead)
- ⚠️ No user session management (uses localStorage)
- ⚠️ No password hashing (stores plain text - SECURITY ISSUE!)
- ⚠️ No request validation middleware
- ⚠️ Limited error logging

### Recommended Next Steps:
1. Implement password hashing (bcrypt or similar)
2. Add prepared statements for 100% SQL injection prevention
3. Implement session management
4. Add input validation middleware
5. Create base Controller class for shared functionality
6. Add comprehensive logging
7. Implement authentication middleware
8. Add CORS headers for API security

## File Summary

### Models (3 files)
- `Database.php` - Connection manager
- `User.php` - User operations (~150 lines)
- `Post.php` - Post operations (~200 lines)
- `Cart.php` - Cart operations (~150 lines)

### Controllers (3 files)
- `UserController.php` - User routes (~80 lines)
- `PostController.php` - Post routes (~120 lines)
- `CartController.php` - Cart routes (~100 lines)

### Core
- `index.php` - Router (~30 lines)

### Views
- `nutrinova_Final_Website.html` - Main HTML template
- `styles.css` - All styling
- `script.js` - Client-side logic (UPDATED for MVC routes)

## Conclusion

The NutriNova project has been successfully refactored into a professional MVC architecture. This structure:
- ✅ Follows industry best practices
- ✅ Improves code maintainability
- ✅ Increases scalability
- ✅ Enhances security
- ✅ Facilitates team collaboration
- ✅ Simplifies testing and debugging

The application now has a solid foundation for adding new features and scaling the platform!
