# NutriNova MVC - Quick Start Guide

## Project Structure at a Glance

```
Integration_FINAL_NEW_KN/
├── models/          ← Database operations happen here
├── controllers/     ← Request handling happens here  
├── views/           ← HTML/CSS/JS (presentation)
├── index.php        ← Central router - START HERE
├── styles.css       ← All styling
└── script.js        ← Frontend JavaScript
```

## Quick Reference

### To Access the Application
- **URL**: `http://localhost/Integration_FINAL_NEW_KN/`
- **Entry Point**: All requests go through `index.php`

### Three-Layer Request Flow
```
View (HTML/JS) 
    ↓ fetch('index.php?action=...')
Controller (routes & handles)
    ↓ calls Model methods
Model (database operations)
    ↓ returns response
Controller (formats JSON)
    ↓
View (displays result)
```

### Key Files Organization

| Layer | Location | Purpose |
|-------|----------|---------|
| **Views** | Root directory | `nutrinova_Final_Website.html`, `styles.css`, `script.js` |
| **Controllers** | `/controllers/` | Route requests to models |
| **Models** | `/models/` | Handle database and business logic |
| **Router** | `index.php` | Direct requests to right controller |

## Common Tasks

### 1. Adding a Product to Cart
- **Trigger**: User clicks "Add to Cart" button
- **Files Involved**: 
  - `script.js` - `addToCart()` function calls `/index.php?action=add_to_cart`
  - `CartController.php` - Handles the request
  - `Cart.php` model - Inserts into database
  - `panier` table - Stores the data

### 2. Creating a Forum Post
- **Trigger**: User submits post form
- **Files Involved**:
  - `script.js` - `submitPost()` → calls `/index.php?action=create_post`
  - `PostController.php` - Validates & processes
  - `Post.php` model - Saves to database
  - `post` table - Stores posts

### 3. User Registration
- **Trigger**: Registration form submission
- **Files Involved**:
  - `script.js` - `handleRegister()` → `/index.php?action=register`
  - `UserController.php` - Processes registration
  - `User.php` model - Validates & inserts user
  - `users` table - Stores user data

### 4. Logging In
- **Trigger**: Login form submission
- **Files Involved**:
  - `script.js` - `handleLogin()` → `/index.php?action=login`
  - `UserController.php` - Authenticates
  - `User.php` model - Checks credentials
  - `users` table - Queries for user

## API Endpoints

All endpoints are accessed via: `index.php?action=[ACTION_NAME]`

### User Authentication
```
POST /index.php?action=register          # Create account
POST /index.php?action=login             # Login user
```

### Forum Posts
```
POST /index.php?action=create_post       # Create new post
GET  /index.php?action=get_all_posts     # Load all posts
POST /index.php?action=update_post_123   # Edit post 123
POST /index.php?action=delete_post_123   # Delete post 123
```

### Shopping Cart
```
POST /index.php?action=add_to_cart       # Add product
GET  /index.php?action=get_cart          # View cart
POST /index.php?action=remove_from_cart  # Remove item
POST /index.php?action=clear_cart        # Empty cart
```

## Database Tables

### `users`
```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    Nom VARCHAR(255) NOT NULL,
    Prenom VARCHAR(255) NOT NULL,
    Email VARCHAR(255) UNIQUE NOT NULL,
    Mot_de_passe VARCHAR(255) NOT NULL,
    Taille_cm INT,
    Poids_kg FLOAT,
    Objectif VARCHAR(255),
    Niveau_sportif VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### `post`
```sql
CREATE TABLE post (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom_auteur VARCHAR(255) NOT NULL,
    titre_post VARCHAR(255) NOT NULL,
    contenu_post TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### `panier`
```sql
CREATE TABLE panier (
    id INT PRIMARY KEY AUTO_INCREMENT,
    Nom VARCHAR(255) NOT NULL,
    Prix FLOAT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## Response Format

All controllers return **JSON responses**:

### Success
```json
{
    "success": true,
    "message": "Action completed",
    "id": 123,
    "data": { /* optional */ }
}
```

### Error
```json
{
    "success": false,
    "error": "Error description",
    "code": "ERROR_CODE"
}
```

## Testing the Setup

### 1. Test Database Connection
Visit: `http://localhost/Integration_FINAL_NEW_KN/`
- Tables should auto-create on first access
- Check browser console for any errors

### 2. Test Registration
- Fill registration form
- Submit
- Check browser network tab in DevTools
- Should see response from `/index.php?action=register`

### 3. Test Login
- Log in with created account
- Should see success message
- User stored in localStorage

### 4. Test Posts
- Go to Community section
- Create a post
- Should POST to `/index.php?action=create_post`
- Post should appear in list

### 5. Test Cart
- Go to Shop
- Add products
- View cart
- Should POST to `/index.php?action=add_to_cart`

## Debugging Tips

### Check Browser Console
```
F12 → Console tab
Look for: fetch calls, errors, responses
```

### Check Network Tab
```
F12 → Network tab
Monitor: All API calls to index.php
View: Request/Response data
```

### Check Server Logs
```
XAMPP → Apache logs show any PHP errors
```

### Test Directly
```
POST to: index.php?action=register
With: nom=John&prenom=Doe&email=john@example.com&mot_de_passe=123456

Should return JSON response
```

## Common Issues & Solutions

### Issue: 404 on API Calls
- ✅ Solution: Make sure URL includes `?action=...`
- ✅ Check spelling of action name

### Issue: Database Connection Error
- ✅ Solution: Ensure XAMPP MySQL is running
- ✅ Check database name: `integration_nutrition_ai`

### Issue: "Table doesn't exist" Error
- ✅ Solution: Tables auto-create on first use
- ✅ Try accessing the page once, then try again

### Issue: CORS Error
- ✅ Solution: Not needed for same-origin requests
- ✅ Both HTML and API are same domain

### Issue: Login Not Working
- ✅ Solution: Check email/password match in database
- ✅ Check localStorage for user data

## Next Steps for Development

1. **Add More Models**: Create `Notification.php`, `Recipe.php`, etc.
2. **Add More Controllers**: Create corresponding controllers
3. **Update Router**: Add new conditions in `index.php`
4. **Enhance Security**: Add password hashing, prepared statements
5. **Add Validation**: Input validation middleware
6. **Implement Logging**: Track errors and activities
7. **Add Tests**: Unit tests for models, functional tests for APIs

## File Reference

### Critical Files (Don't Delete)
- ✋ `index.php` - Router (APPLICATION WILL BREAK)
- ✋ `models/Database.php` - Other models depend on it
- ✋ `nutrinova_Final_Website.html` - Main UI
- ✋ `script.js` - Frontend logic

### Safe to Modify
- ✏️ Models - Add methods, extend functionality
- ✏️ Controllers - Add routes, enhance logic
- ✏️ Views - Update UI/styling
- ✏️ script.js - Add client logic

### Legacy Files (Backup Only)
- 📦 `register_user.php` - Replaced by UserController
- 📦 `login_check.php` - Replaced by UserController
- 📦 `save_post.php` - Replaced by PostController
- 📦 etc. - All old controller files

## Getting Help

1. **Check this guide** - Most answers are here
2. **Read `MVC_ARCHITECTURE.md`** - Detailed documentation
3. **Check browser console** - Errors are logged there
4. **Check Network tab** - See what API calls are made
5. **Review model files** - Logic is well-commented
6. **Review controller files** - Request handling is clear

---

**Happy Coding! 🚀**
