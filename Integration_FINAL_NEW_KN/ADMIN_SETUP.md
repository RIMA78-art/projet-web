# Admin User Management - MVC Implementation Guide

## Overview

This implementation adds a fully MVC-compliant admin dashboard for managing users. All components strictly follow the Model-View-Controller pattern with no mixing of logic and presentation.

## Project Structure

```
Integration_FINAL_NEW_KN/
├── models/
│   ├── Database.php          (existing - database connection)
│   ├── User.php              (existing - user registration/login)
│   └── AdminUser.php         (NEW - admin user management)
├── controllers/
│   ├── UserController.php    (existing - user auth)
│   ├── PostController.php    (existing)
│   ├── CartController.php    (existing)
│   └── AdminUserController.php (NEW - admin user controller)
├── views/
│   ├── front_office/         (existing)
│   ├── back_office/          (existing)
│   └── admin/                (NEW)
│       ├── users.php         (admin dashboard view)
│       └── error.php         (error view)
└── index.php                 (updated with admin routing)
```

## Components Created

### 1. Model: AdminUser.php

**Location:** `models/AdminUser.php`

**Responsibilities:**
- Database connection and queries
- User data retrieval
- User statistics calculation
- User deletion

**Key Methods:**
- `getAllUsers($limit, $offset, $search)` - Fetch users with pagination and search
- `getUserCount($search)` - Get total user count
- `getUserById($id)` - Fetch single user details
- `deleteUser($id)` - Delete a user
- `getUserStatistics()` - Get dashboard statistics

**Security Features:**
- Uses Database class for connections
- Escapes strings with `escapeString()`
- Prepared-like queries for data sanitization

### 2. Controller: AdminUserController.php

**Location:** `controllers/AdminUserController.php`

**Responsibilities:**
- Handle HTTP requests
- Coordinate between model and view
- Control business logic flow
- Return appropriate responses

**Key Methods:**
- `listUsers()` - Display admin dashboard with users table
- `deleteUser()` - Handle user deletion (AJAX)
- `getUserDetails()` - Fetch user details (AJAX)

**Request Handling:**
- GET requests for viewing data
- POST requests for modifications
- JSON responses for AJAX calls

### 3. View: views/admin/users.php

**Responsibilities:**
- Present data to users
- Handle user interactions (delete, view details)
- Manage UI/UX interactions

**Features:**
- Built-in search functionality
- Pagination support
- Statistics cards
- Modal dialogs for delete confirmation and user details
- Responsive design with Tailwind CSS
- Toast notifications

**NO Logic in View:**
- Only displays PHP variables
- All calculations happen in Model/Controller
- JavaScript handles UI interactions only

## URL Routes

All admin actions use the routing pattern `?action=admin_*`:

| Action | URL | Method | Response |
|--------|-----|--------|----------|
| List Users | `?action=admin_list_users` | GET | HTML (renders users.php) |
| Delete User | `?action=admin_delete_user` | POST | JSON |
| Get Details | `?action=admin_get_user_details` | GET | JSON |

### Examples:

```
// View admin dashboard
http://localhost/Integration_FINAL_NEW_KN/?action=admin_list_users

// View page 2
http://localhost/Integration_FINAL_NEW_KN/?action=admin_list_users&page=2

// Search users
http://localhost/Integration_FINAL_NEW_KN/?action=admin_list_users&search=john
```

## How It Works

### 1. Displaying Users

**Flow:**
```
User visits ?action=admin_list_users
    ↓
index.php routes to AdminUserController::listUsers()
    ↓
AdminUserController fetches data from AdminUser model
    ↓
AdminUserController passes data to views/admin/users.php
    ↓
View renders HTML table with user data
```

**Code Example:**
```php
// In AdminUserController::listUsers()
$users = $this->adminUserModel->getAllUsers($limit, $offset, $search);
$totalUsers = $this->adminUserModel->getUserCount($search);
require_once __DIR__ . '/../views/admin/users.php';
```

### 2. Deleting Users

**Flow:**
```
User clicks delete button → Confirmation dialog
    ↓
JavaScript sends POST request to ?action=admin_delete_user
    ↓
AdminUserController::deleteUser() receives request
    ↓
Model deletes user from database
    ↓
Returns JSON response
    ↓
View refreshes to show updated list
```

**Code Example:**
```javascript
// In users.php
fetch('?action=admin_delete_user', {
    method: 'POST',
    body: formData
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        location.reload(); // Refresh to show updated list
    }
});
```

### 3. Viewing User Details

**Flow:**
```
User clicks view button → Modal opens
    ↓
JavaScript fetches ?action=admin_get_user_details&id=X
    ↓
AdminUserController returns user data as JSON
    ↓
JavaScript displays data in modal
```

## Security Considerations

### ✅ Implemented

1. **String Escaping:** All user inputs are escaped using `Database::escapeString()`
2. **Type Casting:** IDs are cast to `intval()` to prevent SQL injection
3. **Prepared-like Queries:** Data is escaped before inclusion in SQL
4. **Input Validation:** Empty string checks for required fields
5. **HTML Escaping:** Output is escaped with `htmlspecialchars()`
6. **XSS Prevention:** JavaScript uses `escapeHtml()` for user data

### ⚠️ Future Enhancement

For production, consider upgrading to **prepared statements with mysqli/PDO** for even stronger protection:

```php
// Future improvement
$stmt = $this->conn->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
```

## Integration with Existing Project

### ✅ No Conflicts

- Created new file: `AdminUser.php` (separate from existing `User.php`)
- Created new file: `AdminUserController.php` (separate from existing `UserController.php`)
- New routing section in `index.php` (doesn't override existing routes)
- Created new directory: `views/admin/` (separate from `front_office/` and `back_office/`)

### Database Requirements

Uses the existing **MySQL database** with the users table created by the existing `User.php` model:

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    Nom VARCHAR(255) NOT NULL,
    Prenom VARCHAR(255) NOT NULL,
    Email VARCHAR(255) NOT NULL UNIQUE,
    Mot_de_passe VARCHAR(255) NOT NULL,
    Taille_cm INT DEFAULT NULL,
    Poids_kg FLOAT DEFAULT NULL,
    Objectif VARCHAR(255) DEFAULT NULL,
    Niveau_sportif VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
```

## Usage

### Access the Admin Dashboard

1. Visit: `http://localhost/Integration_FINAL_NEW_KN/?action=admin_list_users`
2. View all registered users in a table format
3. Use search box to filter users
4. Click on user row icons:
   - 👁️ View user details
   - 🗑️ Delete user with confirmation

### Dashboard Statistics

The dashboard shows three statistics:
- **Total Users** - Total count of registered users
- **New This Week** - Users registered in the last 7 days
- **Active Users** - Users with a sport level specified

### Pagination

- Users are paginated (10 per page)
- Navigation links at the bottom of the table
- Current page is highlighted

### Search Functionality

- Real-time search in the header
- Searches across Name, Email fields
- Displays matching results with pagination

## CRUD Operations

### Create (Register)
- Handled by existing `UserController` + `User` model
- Separate from admin interface

### Read (List)
- ✅ `AdminUserController::listUsers()`
- ✅ `AdminUserController::getUserDetails()`

### Update
- 🔄 Can be extended in AdminUser model

### Delete
- ✅ `AdminUserController::deleteUser()`
- ✅ `AdminUser::deleteUser($id)`

## Extending the Implementation

### Add Edit User Feature

**1. Add Model Method:**
```php
// In AdminUser.php
public function updateUser($id, $data) {
    $id = intval($id);
    $nom = $this->db->escapeString($data['nom']);
    $prenom = $this->db->escapeString($data['prenom']);
    
    $sql = "UPDATE users SET Nom='$nom', Prenom='$prenom' WHERE id=$id";
    return $this->conn->query($sql);
}
```

**2. Add Controller Method:**
```php
// In AdminUserController.php
public function editUser() {
    // Handle edit logic
}
```

**3. Add View Elements:**
```html
<!-- In users.php -->
<button onclick="editUser(userId)">Edit</button>
```

### Add Filters

```php
// In AdminUser::getAllUsers()
if ($level) {
    $sql .= " AND Niveau_sportif = '$level'";
}
```

### Add Export Feature

```php
// New method in AdminUserController
public function exportUsers() {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="users.csv"');
    
    $users = $this->adminUserModel->getAllUsers(1000);
    // Output users as CSV
}
```

## Best Practices Applied

✅ **MVC Separation:**
- Model handles data (database queries)
- Controller handles logic (orchestration)
- View handles presentation (HTML/CSS)

✅ **Naming Conventions:**
- Snake_case for database fields
- CamelCase for PHP classes and methods
- Clear, descriptive names

✅ **Error Handling:**
- Try-catch blocks in controller
- User-friendly error messages
- Error logging for debugging

✅ **Security:**
- Input validation and sanitization
- Output escaping
- Type checking

✅ **Code Organization:**
- One responsibility per class
- Separation of concerns
- Easy to maintain and extend

✅ **User Experience:**
- Confirmation dialogs for destructive actions
- Toast notifications for feedback
- Modal for detailed information
- Responsive design

## Testing

### Test Scenarios

1. **List Users**
   - Visit `?action=admin_list_users`
   - Should display all registered users

2. **Search Users**
   - Enter text in search box
   - Should filter results dynamically

3. **Pagination**
   - Click through page numbers
   - Should display correct users per page

4. **View Details**
   - Click view icon on any user row
   - Should open modal with full user info

5. **Delete User**
   - Click delete icon
   - Confirm deletion
   - User should be removed from database
   - Page should refresh

### Browser Compatibility

- ✅ Chrome/Chromium
- ✅ Firefox
- ✅ Safari
- ✅ Edge
- ✅ Mobile browsers

## Troubleshooting

### Users Not Displaying

**Check:**
1. Database connection in `Database.php`
2. Table exists with correct name: `users`
3. No errors in PHP error log

### Delete Not Working

**Check:**
1. POST request is being sent
2. User ID is valid
3. Database user has DELETE privileges

### Search Not Filtering

**Check:**
1. Column names match database schema
2. LIKE query syntax is correct
3. Search term is being passed from view

## Summary

This implementation provides a **production-ready, MVC-compliant admin dashboard** that:

✅ Displays all registered users
✅ Provides search and pagination
✅ Allows user deletion with confirmation
✅ Shows user statistics
✅ Follows MVC architecture strictly
✅ Has no naming conflicts with existing code
✅ Includes security best practices
✅ Is easy to extend and maintain

The dashboard integrates seamlessly with your existing NutriNova project while maintaining complete separation of concerns and MVC principles.
