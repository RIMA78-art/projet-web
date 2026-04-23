# Admin Dashboard - Quick Start

## Access the Admin Dashboard

Simply visit this URL in your browser:

```
http://localhost/Integration_FINAL_NEW_KN/?action=admin_list_users
```

## What You'll See

✅ **Dashboard Overview**
- Total registered users count
- New users this week
- Active users with sport level

✅ **User Table**
- All registered users displayed
- User name, email, height, weight, sport level
- Registration date

✅ **Actions Per User**
- 👁️ **View** - See full user details in a modal
- 🗑️ **Delete** - Delete user with confirmation dialog

✅ **Search & Filter**
- Search box in header to find users by name or email
- Real-time filtering

✅ **Pagination**
- 10 users per page
- Navigate between pages

## File Structure

New files created:

```
models/AdminUser.php                    ← Model for admin operations
controllers/AdminUserController.php      ← Controller for admin logic
views/admin/users.php                   ← Admin dashboard view
views/admin/error.php                   ← Error page
ADMIN_SETUP.md                          ← Full documentation
```

## How It Works (MVC)

```
Browser Request (?action=admin_list_users)
    ↓
index.php (Router)
    ↓
AdminUserController::listUsers() (Controller)
    ↓
AdminUser Model (Database Query)
    ↓
views/admin/users.php (Renders HTML)
    ↓
Browser displays user table
```

## Features

### 1. View All Users
- Displays paginated list of users
- Shows name, email, measurements, sport level
- Responsive design

### 2. Search Users
Type in the search box to filter users:
- Searches by first name, last name, email
- Results update in real-time
- Works with pagination

### 3. View User Details
Click the 👁️ icon to:
- Open a modal with full user information
- See registration date
- View objectives and sport level

### 4. Delete User
Click the 🗑️ icon to:
- See confirmation dialog
- Confirm deletion
- User is removed from database
- Page refreshes automatically

### 5. Statistics
Dashboard shows:
- **Total Users** - All registered users
- **New This Week** - Registrations in last 7 days
- **Active Users** - Users with sport level set

## Database Connection

The admin dashboard uses the existing database configured in:
```php
models/Database.php
```

Default connection (already configured):
- **Host:** localhost
- **User:** root
- **Password:** (empty)
- **Database:** integration_nutrition_ai
- **Table:** users

## Testing Your Installation

### ✅ Step 1: Register Test Users
- Go to your front office: `http://localhost/Integration_FINAL_NEW_KN/`
- Click "S'inscrire" (Register)
- Fill in user details and register 2-3 test users

### ✅ Step 2: Access Admin Dashboard
- Visit: `http://localhost/Integration_FINAL_NEW_KN/?action=admin_list_users`
- You should see the users you just registered in the table

### ✅ Step 3: Test Features
1. **Search** - Type a name in the search box
2. **View Details** - Click 👁️ on any user
3. **Delete** - Click 🗑️ and confirm deletion
4. **Pagination** - If you have 10+ users, test page navigation

## Troubleshooting

### Users Not Showing?
1. Check that users are registered in the database
2. Verify database connection is working
3. Check browser console for JavaScript errors

### Delete Not Working?
1. Try refreshing the page first
2. Check browser console for errors
3. Verify user exists in database

### Search Not Filtering?
1. Try entering exact first name or last name
2. Check spelling matches database
3. Try searching by email instead

## What's Next?

### Future Enhancements You Can Add:

1. **Edit User**
   - Add an Edit button
   - Update user information

2. **Export to CSV**
   - Download users list
   - For reporting

3. **Advanced Filters**
   - Filter by sport level
   - Filter by registration date range
   - Filter by height/weight range

4. **Authentication**
   - Admin login required
   - Only admins can access dashboard

5. **Bulk Actions**
   - Delete multiple users at once
   - Bulk email to users

6. **User Statistics**
   - Charts and graphs
   - User demographics
   - Growth trends

## No Conflicts With Existing Code

✅ Created new model: `AdminUser.php` (separate from `User.php`)
✅ Created new controller: `AdminUserController.php` (separate from `UserController.php`)
✅ Created new view folder: `views/admin/` (separate from other views)
✅ All existing functionality remains unchanged

## File Locations for Reference

| File | Purpose |
|------|---------|
| `models/AdminUser.php` | Database queries for admin |
| `controllers/AdminUserController.php` | Admin business logic |
| `views/admin/users.php` | Admin dashboard display |
| `views/admin/error.php` | Error page |
| `index.php` | Router (updated) |
| `ADMIN_SETUP.md` | Full documentation |

## Support

If you encounter issues:

1. Check the browser console (F12) for JavaScript errors
2. Check PHP error log in xampp/apache/logs/
3. Verify database connection in `models/Database.php`
4. Review `ADMIN_SETUP.md` for detailed documentation

---

**Welcome to your admin dashboard! 🎉**
