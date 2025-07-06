# Aturan Chat System - CodingFirst Laravel

## 📝 Aturan Chat Berdasarkan Role

Sistem chat telah dikonfigurasi dengan aturan akses yang ketat berdasarkan role pengguna:

### 🟢 **Parent (Orang Tua)**
- ✅ Hanya bisa chat dengan **anak-anaknya** 
- ✅ Hanya bisa chat dengan **admin**
- ❌ Tidak bisa chat dengan guru
- ❌ Tidak bisa chat dengan parent lain

### 🔵 **Student (Anak/Siswa)**
- ✅ Bisa chat dengan **parent mereka**
- ✅ Bisa chat dengan **admin**
- ✅ Bisa chat dengan **guru yang mengajar di kelas yang dibelinya**
- ❌ Tidak bisa chat dengan siswa lain (classmates)
- ❌ Tidak bisa chat dengan guru yang tidak mengajar mereka

### 🟡 **Teacher (Guru)**
- ✅ Bisa chat dengan **admin**
- ✅ Bisa chat dengan **murid yang dia ajari**
- ✅ Bisa chat dengan **guru lainnya**
- ❌ Tidak bisa chat dengan murid yang tidak dia ajari

### 🟠 **Admin**
- ✅ Bisa chat dengan **siapa saja** (kecuali Super Admin)
- ✅ Hub komunikasi utama untuk semua role

### 🟣 **Finance**
- ✅ Hanya bisa chat dengan **admin**
- ❌ Tidak bisa chat dengan role lain

### 🔴 **Super Admin**
- ❌ **TIDAK BISA CHAT** sama sekali
- ❌ Tidak memiliki akses ke fitur chat
- ❌ Link chat tidak muncul di sidebar

## 🛠️ Implementasi Teknis

### 1. Controller Protection
- `ChatController::index()` - Blokir Super Admin
- `ChatController::show()` - Blokir Super Admin
- `ChatController::startConversation()` - Blokir Super Admin
- `ChatController::sendMessage()` - Blokir Super Admin

### 2. Available Contacts Logic
Method `getAvailableContacts()` dikonfigurasi untuk setiap role:

```php
// Super Admin: return empty collection
// Student: parents + admins + teaching teachers
// Teacher: admins + taught students + other teachers
// Parent: children + admins
// Finance: admins only
// Admin: everyone except superadmin
```

### 3. Permission Validation
Method `canChatWith()` memvalidasi setiap percakapan:
- Super Admin tidak bisa chat dengan siapa pun
- Tidak ada yang bisa chat dengan Super Admin
- Admin bisa chat dengan semua (kecuali Super Admin)
- Role lain mengikuti aturan `getAvailableContacts()`

### 4. UI Protection
- Sidebar chat link disembunyikan untuk Super Admin
- Finance Communication section terpisah dari Super Admin
- Error handling untuk akses yang tidak diizinkan

## 🔒 Security Features

1. **Route Level Protection**: Middleware role validation
2. **Controller Level Protection**: Explicit Super Admin blocks
3. **Database Level Protection**: Relationship-based contact filtering
4. **UI Level Protection**: Conditional sidebar display

## ✅ Testing Scenarios

### Parent Testing:
- ✅ Dapat melihat anak di contact list
- ✅ Dapat melihat admin di contact list
- ❌ Tidak dapat melihat guru di contact list

### Student Testing:
- ✅ Dapat melihat parent di contact list
- ✅ Dapat melihat admin di contact list
- ✅ Dapat melihat guru yang mengajar mereka
- ❌ Tidak dapat melihat guru lain

### Super Admin Testing:
- ❌ Error 403 saat akses `/chat`
- ❌ Tidak ada link chat di sidebar
- ❌ Error jika mencoba start conversation

## 📊 Relationship Mapping

```
Parent → Children (many-to-many via parent_student)
Parent → Admin (role-based)

Student → Parents (many-to-many via parent_student)
Student → Admin (role-based)
Student → Teachers (via enrollment → class → teacher)

Teacher → Admin (role-based)
Teacher → Students (via teaching_classes → enrollments)
Teacher → Other Teachers (role-based)

Finance → Admin (role-based only)

Admin → Everyone (except Super Admin)

Super Admin → Nobody
```

## 🚀 Deployment Notes

1. Database harus memiliki data parent-student relationships
2. Enrollments harus aktif (status = 'active')
3. Teacher assignments harus sesuai dengan class yang diajar
4. Role assignments harus benar untuk semua users

---

**Catatan**: Aturan ini dapat dimodifikasi dengan mengubah logic di `ChatController::getAvailableContacts()` dan `ChatController::canChatWith()`. 