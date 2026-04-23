# File Upload Feature for Posts

## Overview
File upload functionality has been added to the Post creation feature, allowing users to attach files (images, PDFs, Word documents) to their posts with full validation and security measures.

## Architecture

### Backend Components

#### 1. FileUploadHandler Model
**File:** `models/FileUploadHandler.php`

Handles all file operations:
- **File Validation**
  - File size check (max 2MB)
  - Extension whitelist (jpg, jpeg, png, pdf, docx)
  - MIME type validation using `finfo_file()`
  - Error checking on upload

- **File Storage**
  - Unique filename generation using `uniqid()` + timestamp
  - Automatic directory creation if missing
  - Safety: Prevents directory traversal attacks

- **File Deletion** (for future use)
  - Safe deletion of stored files
  - Path sanitization

**Key Methods:**
```php
validateFile($file)     // Validates $_FILES['fichier']
saveFile($file)        // Saves and returns filename
deleteFile($filename)  // Deletes a file safely
```

#### 2. Post Model Updates
**File:** `models/Post.php`

- Added `fichier` column (VARCHAR 255, nullable) to store filename
- Updated `createTable()` to include fichier field
- Updated `create()` method to accept and store fichier parameter
- Updated `getAll()` to retrieve fichier field
- Database maintains referential integrity with foreign keys

#### 3. PostController Updates
**File:** `controllers/PostController.php`

- Imports `FileUploadHandler` class
- Validates file upload in `create()` method:
  - Checks for upload errors
  - Calls `FileUploadHandler->validateFile()`
  - Calls `FileUploadHandler->saveFile()`
  - Returns proper error messages
  - Passes filename to Post model

**Error Handling:**
- If file is invalid, returns JSON error before saving to DB
- If file save fails, returns error without creating post
- Optional file uploads don't break post creation

### Frontend Components

#### 1. HTML Form
**File:** `views/front_office/index.html`

Changes to post modal:
- Wrapped in `<form id="post-form" enctype="multipart/form-data">`
- Added file input: `<input type="file" accept=".jpg,.jpeg,.png,.pdf,.docx">`
- File info display: Shows selected file name and size
- Error display: Shows validation errors

#### 2. JavaScript
**File:** `views/front_office/js/script.js`

**New Functions:**
- `validateFileInput(input)` - Real-time file validation
  - Checks file size (2MB max)
  - Checks file extension
  - Shows/hides error messages
  - Displays file info
  
- Modified `showPostModal()` - Resets file input when opening
- Modified `submitPost()` - Adds file to FormData
- Modified `loadPostsFromDB()` - Displays download links

**Validation Rules (Frontend):**
- File size ≤ 2MB
- Extensions: jpg, jpeg, png, pdf, docx
- Real-time feedback while selecting files

#### 3. Display Logic
When displaying posts:
- If post has fichier, shows download button
- Download link format: `uploads/{filename}`
- Download button: "📥 Télécharger le fichier"
- Styled within post content area

## Database Schema

### Post Table Update
```sql
ALTER TABLE post ADD COLUMN fichier VARCHAR(255) DEFAULT NULL;
```

**New Column:**
- `fichier`: VARCHAR(255), nullable
- Stores just the filename (e.g., `post_660a1b2c_1713355200.jpg`)
- No path stored (all files go to `/uploads/`)

## File Organization

### Directory Structure
```
Integration_FINAL_NEW_KN/
├── uploads/
│   ├── init.php          (creates directory if missing)
│   ├── .htaccess         (security: MIME type headers)
│   ├── index.php         (prevents directory listing)
│   └── post_*.jpg|pdf|...  (uploaded files)
├── models/
│   └── FileUploadHandler.php
├── controllers/
│   └── PostController.php
├── views/
│   └── front_office/
│       ├── index.html
│       └── js/
│           └── script.js
└── database_backup.sql
```

## Security Features

### 1. File Validation
✅ **Server-side validation** (primary)
- Extension whitelist check (not just trusting browser)
- MIME type verification using `finfo_file()`
- File size limit (2MB enforced on server)
- Upload error checking

✅ **Client-side validation** (UX only)
- Browser-level input restriction
- Real-time feedback
- No reliance on client validation

### 2. Filename Safety
✅ Unique filenames prevent:
- Overwriting existing files
- Predictable file names
- Directory traversal attacks

✅ Format: `post_{uniqid}_{timestamp}.{ext}`
- Example: `post_660a1b2c_1713355200.jpg`

### 3. Directory Protection
✅ `.htaccess` file sets MIME types (prevents code execution)
✅ `index.php` prevents directory listing
✅ Init script ensures directory exists with proper permissions

### 4. No Code Execution
✅ Allowed extensions don't execute:
- `.jpg`, `.jpeg`, `.png` - Images only
- `.pdf` - Read-only format
- `.docx` - Office format (text only)
✅ No `.php`, `.exe`, `.js`, `.bat` files allowed

## Validation Flow

### Server-Side Validation (PHP)
```
User Submits Form
↓
PostController->create()
↓
Check $_FILES['fichier'] for UPLOAD_ERR_OK
↓
FileUploadHandler->validateFile()
  - Check size < 2MB
  - Check extension in whitelist
  - Check MIME type with finfo_file()
↓
If valid: FileUploadHandler->saveFile()
  - Generate unique filename
  - Move uploaded file to /uploads/
  - Return filename
↓
Post model->create() saves filename to DB
↓
JSON response to frontend
```

### Client-Side Validation (JavaScript)
```
User selects file in input
↓
onChange event triggers validateFileInput()
  - Check size < 2MB
  - Check extension in whitelist
  - Show error or success message
↓
User submits form
↓
submitPost() validates again before sending
↓
FormData includes file
↓
POST to create_post action
```

## API Endpoints

### Create Post with File
```
POST /index.php?action=create_post
Content-Type: multipart/form-data

Fields:
- nom_auteur (string)
- titre_post (string)
- contenu_post (string)
- fichier (file, optional)

Response:
{
  "success": true/false,
  "message": "...",
  "error": "File upload error...",
  "code": "FILE_VALIDATION_ERROR" | "FILE_SAVE_ERROR" | "..."
}
```

### Download File
```
GET /uploads/{filename}

User sees browser download dialog
```

## User Workflow

### Creating a Post with File
1. User clicks "Créer une nouvelle discussion"
2. Post modal opens
3. User fills title and content
4. User clicks file input and selects file
5. Real-time validation shows:
   - ✅ Filename and size if valid
   - ❌ Error message if invalid
6. User clicks "Poster"
7. Form validates again
8. POST sent with FormData including file
9. Server validates and saves file
10. Filename stored in database
11. Posts reload with download link

### Downloading a File
1. User clicks "📥 Télécharger le fichier"
2. Browser shows save dialog
3. File downloads from `/uploads/{filename}`
4. No page reload

## Error Handling

### Validation Errors
| Scenario | User Feedback |
|----------|---------------|
| File > 2MB | "Fichier trop volumineux (max 2MB)" |
| Invalid extension | "Format non autorisé. Acceptés: JPG, PNG, PDF, DOCX" |
| Invalid MIME type | Backend rejects (not shown to user) |
| Upload failed | "Failed to save file" |

### Response Codes
- `FILE_VALIDATION_ERROR` - File didn't pass validation
- `FILE_SAVE_ERROR` - File moved but couldn't be saved
- `MISSING_FIELDS` - Post fields empty
- `DB_ERROR` - Database save failed

## Backward Compatibility

✅ **No Breaking Changes**
- `fichier` column is nullable (optional)
- Existing posts without files work fine
- Post creation without file still works
- All existing Post functionality preserved

## Performance Considerations

✅ **Efficiency**
- Only validate files that exist
- Fast filename generation with `uniqid()`
- No unnecessary database queries
- MIME type check only on upload

## Future Enhancements

1. **File Management**
   - Delete files when post is deleted
   - Edit post and replace file
   - Support for multiple files

2. **File Types**
   - Expand allowed types (zip, odt, etc.)
   - Image compression/resizing

3. **Storage**
   - Archive old files to cloud storage
   - File versioning

4. **Security**
   - Virus scanning on upload
   - Rate limiting on uploads
   - File access logging

## Testing Checklist

- [ ] Create post without file (works as before)
- [ ] Create post with valid JPG file
- [ ] Create post with valid PNG file
- [ ] Create post with valid PDF file
- [ ] Create post with valid DOCX file
- [ ] Try upload > 2MB (rejected)
- [ ] Try invalid extension like .exe (rejected)
- [ ] File displayed in post after creation
- [ ] Download link works
- [ ] File exists in /uploads/ directory
- [ ] Filename is unique (not overwritten)
- [ ] Can download multiple files from different posts
- [ ] Deleting post doesn't break future uploads

## File Permissions

### Directory Permissions
```bash
chmod 755 /uploads/    # Read and execute for all
```

### File Permissions
```bash
chmod 644 /uploads/*   # Read for all, write for owner
```

## Troubleshooting

### Files not uploading
1. Check `/uploads/` directory exists
2. Check directory is writable (755 or better)
3. Check PHP post_max_size and upload_max_filesize
4. Check server error logs

### Download links broken
1. Verify filename stored correctly in database
2. Check file exists in `/uploads/`
3. Check file permissions (644)
4. Check .htaccess MIME types

### Validation always fails
1. Check MIME type detection with `file_info`
2. Verify extension is in whitelist
3. Check file size with `filesize()`

## Conclusion

File upload functionality is fully integrated with:
- ✅ Comprehensive server-side validation
- ✅ Real-time client-side feedback
- ✅ Secure unique filenames
- ✅ Proper error handling
- ✅ Backward compatibility
- ✅ Easy download mechanism

The implementation maintains clean MVC architecture and integrates seamlessly with existing Post functionality.
