# Troubleshooting Guide: Blade Views Not Rendering (File Corruption Issue)

## Problem Summary
**Issue:** Student views showing raw Blade syntax instead of compiled HTML with proper styling.

**Affected Files:**
- `resources/views/student/reports/index.blade.php`
- `resources/views/student/schedules/index.blade.php`

**Symptoms:**
- Views display raw Blade syntax: `<�x-app-layout>`, `{{ __('My Reports') }}`, `@if`, `@foreach`
- No CSS styling applied
- Layout components not rendered
- Other views in different folders work perfectly

## Root Cause
**File Corruption/Encoding Issues** in specific Blade view files, causing the Blade compiler to fail silently and return raw file content instead of compiled HTML.

## Diagnosis Steps

### Step 1: Verify Layout Component Works
```bash
# Test layout component directly
Route::get('/debug-layout', function () {
    return view('layouts.app');
})->middleware('auth');
```
**Expected:** Should show proper layout with sidebar/navbar.

### Step 2: Test View with Different Route
```bash
# Create identical view with different filename
Route::get('/debug-reports-copy', function () {
    $reports = collect([]);
    return view('debug-reports-copy', compact('reports'));  // New file
})->middleware('auth');
```
**Expected:** If this works but original doesn't, it's file corruption.

### Step 3: Test Blade Compiler Rendering
```bash
Route::get('/debug-view-render', function () {
    try {
        $reports = collect([]);
        $view = view('student.reports.index', compact('reports'));
        return '<div>' . $view->render() . '</div>';
    } catch (Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
})->middleware('auth');
```
**Expected:** Should show if Blade compiler produces raw syntax or proper HTML.

### Step 4: Check User Permissions
```bash
Route::get('/debug-role', function () {
    $user = auth()->user();
    return [
        'user' => $user->name,
        'roles' => $user->getRoleNames(),
        'hasStudentRole' => $user->hasRole('student')
    ];
})->middleware('auth');
```

## Diagnostic Results Interpretation

| Test Result | Diagnosis |
|-------------|-----------|
| Layout works + Copy works + Original fails | **File Corruption** ✅ |
| All views fail | Layout component issue |
| Role check fails | Permission/middleware issue |
| Render shows raw Blade | Blade compiler not processing file |

## Solution: Replace Corrupted Files

### Step 1: Backup Original (Optional)
```bash
cp resources/views/student/reports/index.blade.php resources/views/student/reports/index.blade.php.backup
```

### Step 2: Delete Corrupted File
```bash
rm resources/views/student/reports/index.blade.php
```

### Step 3: Recreate Clean File
Create new file with clean, properly encoded content:

```blade
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Reports') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- Clean, fresh content -->
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
```

### Step 4: Clear All Caches
```bash
php artisan view:clear
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

## Prevention Tips

1. **File Encoding:** Always use UTF-8 without BOM
2. **Copy/Paste:** Avoid copying from rendered web pages or documents
3. **Editor Settings:** Use proper code editor with correct encoding
4. **Version Control:** Commit working files before major changes
5. **Backup:** Keep backups of working view files

## Common Causes of File Corruption

1. **Encoding Issues:** Mixed encodings (UTF-8 vs UTF-16 vs ASCII)
2. **Copy/Paste Artifacts:** Hidden characters from web browsers/documents
3. **Editor Problems:** Text editors adding BOM or changing line endings
4. **Transfer Issues:** FTP transfers in wrong mode (binary vs text)
5. **Character Set Problems:** Non-ASCII characters in wrong encoding

## Warning Signs

- Raw Blade syntax visible in browser
- CSS not loading despite assets being compiled
- Layout components showing as literal text
- Strange characters (�) appearing in output
- File works in one location but not another

## Quick Fix Checklist

- [ ] Test layout component independently
- [ ] Create test view with identical content in different file
- [ ] Check user roles and permissions
- [ ] Verify Blade compiler output
- [ ] Replace corrupted files with clean versions
- [ ] Clear all Laravel caches
- [ ] Test original routes

## Related Issues

This fix resolves:
- Views showing raw Blade syntax
- Missing CSS styling on specific pages
- Layout components not rendering
- Blade directives (@if, @foreach) appearing as text

## Test After Fix

1. Access original routes: `/student/reports`, `/student/schedules`
2. Verify proper layout with sidebar and navbar
3. Check that Blade directives are compiled
4. Confirm CSS styling is applied
5. Test navigation from dashboard

---

**Created:** December 2024  
**Last Updated:** December 2024  
**Status:** Resolved ✅ 