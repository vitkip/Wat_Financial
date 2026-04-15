# ຄູ່ມືການຕິດຕັ້ງ — Wat Financial Dashboard

> ລະບົບບັນຊີການເງິນສ່ວນຕົວ · PHP MVC + MySQL + TailwindCSS · ແລ່ນໃນ XAMPP

---

## ສາລະບານ

1. [ຂໍ້ກຳນົດເບື້ອງຕົ້ນ](#1-ຂໍ້ກຳນົດເບື້ອງຕົ້ນ)
2. [ການຕິດຕັ້ງ XAMPP](#2-ການຕິດຕັ້ງ-xampp)
3. [ການຕິດຕັ້ງ Node.js](#3-ການຕິດຕັ້ງ-nodejs)
4. [ການດາວໂຫຼດໂປຣເຈັກ](#4-ການດາວໂຫຼດໂປຣເຈັກ)
5. [ການຕັ້ງຄ່າຖານຂໍ້ມູນ](#5-ການຕັ້ງຄ່າຖານຂໍ້ມູນ)
6. [ການຕັ້ງຄ່າໂປຣເຈັກ](#6-ການຕັ້ງຄ່າໂປຣເຈັກ)
7. [ການ Build TailwindCSS](#7-ການ-build-tailwindcss)
8. [ການເປີດໃຊ້ງານລະບົບ](#8-ການເປີດໃຊ້ງານລະບົບ)
9. [ການ Login ຄັ້ງທຳອິດ](#9-ການ-login-ຄັ້ງທຳອິດ)
10. [ການຕັ້ງຄ່າ Apache (Virtual Host — ທາງເລືອກ)](#10-ການຕັ້ງຄ່າ-apache-virtual-host--ທາງເລືອກ)
11. [ການແກ້ໄຂບັນຫາທົ່ວໄປ](#11-ການແກ້ໄຂບັນຫາທົ່ວໄປ)
12. [ໂຄງສ້າງໄດເຣັກທໍຣີ](#12-ໂຄງສ້າງໄດເຣັກທໍຣີ)

---

## 1. ຂໍ້ກຳນົດເບື້ອງຕົ້ນ

ກ່ອນຕິດຕັ້ງ ຕ້ອງມີຊອບແວດັ່ງຕໍ່ໄປນີ້:

| ຊອບແວ | ເວີຊັນຂັ້ນຕ່ຳ | ໜ້າທີ່ |
|--------|--------------|--------|
| **XAMPP** | 8.1+ | Apache Web Server + MySQL + PHP |
| **Node.js** | 18+ | Build TailwindCSS |
| **npm** | 9+ | ຈັດການ package ສຳລັບ CSS |
| **Git** | ໃດກໍໄດ້ | ດາວໂຫຼດໂຄດ (ທາງເລືອກ) |

**ກວດສອບ PHP ທີ່ຕ້ອງການ:**
- PHP PDO extension (MySQL)
- PHP `mbstring` extension (Lao script support)
- PHP `session` extension

---

## 2. ການຕິດຕັ້ງ XAMPP

### macOS
1. ດາວໂຫຼດ XAMPP ທີ່ [apachefriends.org](https://www.apachefriends.org)
2. ເປີດໄຟລ໌ `.dmg` ແລ້ວລາກ XAMPP ໄປໃສ່ `/Applications`
3. ເປີດ **XAMPP Control Panel** ຈາກ `/Applications/XAMPP/manager-osx.app`
4. ກົດ **Start** ໃຫ້ທັງ **Apache** ແລ**MySQL**

### Windows
1. ດາວໂຫຼດ XAMPP installer `.exe` ທີ່ [apachefriends.org](https://www.apachefriends.org)
2. ລັນ installer ແລ້ວໝາຍ **Apache**, **MySQL**, **PHP** ໄວ້
3. ຕິດຕັ້ງໃສ່ `C:\xampp` (ແນະນຳ)
4. ເປີດ **XAMPP Control Panel** ແລ້ວກົດ **Start** ທັງ Apache ແລ MySQL

### ກວດສອບວ່າໃຊ້ງານໄດ້
ເປີດ browser ໄປທີ່ `http://localhost` — ຕ້ອງເຫັນໜ້າ XAMPP Dashboard.

---

## 3. ການຕິດຕັ້ງ Node.js

1. ດາວໂຫຼດ Node.js LTS (ເວີຊັນ 18 ຂຶ້ນໄປ) ທີ່ [nodejs.org](https://nodejs.org)
2. ຕິດຕັ້ງຕາມຂັ້ນຕອນ installer
3. ກວດສອບໃນ terminal:

```bash
node --version   # ຕ້ອງຂຶ້ນ v18.x.x ຫຼື ສູງກວ່າ
npm --version    # ຕ້ອງຂຶ້ນ 9.x.x ຫຼື ສູງກວ່າ
```

---

## 4. ການດາວໂຫຼດໂປຣເຈັກ

### ທາງເລືອກ A — ໃຊ້ Git Clone
```bash
cd /Applications/XAMPP/xamppfiles/htdocs        # macOS
# ຫຼື
cd C:\xampp\htdocs                               # Windows

git clone <REPOSITORY_URL> Wat_Financial
```

### ທາງເລືອກ B — ດາວໂຫຼດ ZIP
1. ດາວໂຫຼດໄຟລ໌ `.zip` ຂອງໂປຣເຈັກ
2. ແຕກໄຟລ໌ໄດ້ folder ຊື່ `Wat_Financial`
3. ຍ້າຍ folder ໄປວາງໃນ:
   - **macOS:** `/Applications/XAMPP/xamppfiles/htdocs/`
   - **Windows:** `C:\xampp\htdocs\`

ຜົນຮັບ: ໂຄດຕ້ອງຢູ່ທີ່ `htdocs/Wat_Financial/`

---

## 5. ການຕັ້ງຄ່າຖານຂໍ້ມູນ

### ຂັ້ນຕອນທີ 1 — ເປີດ phpMyAdmin
ໄປທີ່ `http://localhost/phpmyadmin` ໃນ browser.

### ຂັ້ນຕອນທີ 2 — ສ້າງຖານຂໍ້ມູນ (Schema ຫຼັກ)
1. ກົດ **SQL** tab ໃນ phpMyAdmin
2. Copy ທັງໝົດ ເນື້ອໃນຈາກໄຟລ໌ `database/schema.sql`
3. ວາງໃສ່ SQL box ແລ້ວກົດ **Go**

ໄຟລ໌ນີ້ຈະ:
- ສ້າງ database `wat_financial`
- ສ້າງ tables: `categories`, `transactions`, `budgets`
- ໃສ່ຂໍ້ມູນຕົວຢ່າງ (categories, transactions, budgets)

### ຂັ້ນຕອນທີ 3 — ລັນ Migration (Tables ເພີ່ມເຕີມ)
1. ກົດ database `wat_financial` ໃນ sidebar ຊ້າຍ
2. ກົດ **SQL** tab
3. Copy ທັງໝົດ ເນື້ອໃນຈາກ `database/migrate_add_tables.sql`
4. ວາງໃສ່ SQL box ແລ້ວກົດ **Go**

ໄຟລ໌ migration ຈະສ້າງ tables ເພີ່ມ:
- `users` — ຜູ້ໃຊ້ / ການ login
- `settings` — ການຕັ້ງຄ່າທີ່ປ່ຽນໄດ້
- `recurring_transactions` — ລາຍການທີ່ເກີດຊ້ຳ
- `savings_goals` — ເປົ້າໝາຍການເງິນ

### ທາງເລືອກ — ໃຊ້ Command Line (ສຳລັບຜູ້ຊ່ຽວຊານ)
```bash
# macOS
/Applications/XAMPP/xamppfiles/bin/mysql -u root < /Applications/XAMPP/xamppfiles/htdocs/Wat_Financial/database/schema.sql
/Applications/XAMPP/xamppfiles/bin/mysql -u root < /Applications/XAMPP/xamppfiles/htdocs/Wat_Financial/database/migrate_add_tables.sql

# Windows
C:\xampp\mysql\bin\mysql.exe -u root < C:\xampp\htdocs\Wat_Financial\database\schema.sql
C:\xampp\mysql\bin\mysql.exe -u root < C:\xampp\htdocs\Wat_Financial\database\migrate_add_tables.sql
```

---

## 6. ການຕັ້ງຄ່າໂປຣເຈັກ

ເປີດໄຟລ໌ `config/config.php` ແລ້ວຕັ້ງຄ່າດັ່ງນີ້:

```php
// ── Database ─────────────────────────────────────────────────
define('DB_HOST', 'localhost');   // ປົກກະຕິບໍ່ຕ້ອງປ່ຽນ
define('DB_NAME', 'wat_financial');
define('DB_USER', 'root');        // XAMPP default username
define('DB_PASS', '');            // XAMPP default: ບໍ່ມີ password
define('DB_CHARSET', 'utf8mb4');

// ── App Environment ──────────────────────────────────────────
define('APP_ENV', 'development'); // ປ່ຽນເປັນ 'production' ຕອນ deploy
```

> **ໝາຍເຫດ:** XAMPP ຕິດຕັ້ງໃໝ່ MySQL username ເລີ່ມຕົ້ນແມ່ນ `root` ແລ password ຫວ່າງເປົ່າ. ຖ້າທ່ານຕັ້ງ password MySQL ໄວ້ແລ້ວ ໃຫ້ໃສ່ຢູ່ `DB_PASS`.

---

## 7. ການ Build TailwindCSS

ລະບົບໃຊ້ TailwindCSS v3 ທີ່ຕ້ອງ build ກ່ອນໃຊ້ງານ.

### ຂັ້ນຕອນທີ 1 — ເຂົ້າ folder ໂປຣເຈັກ
```bash
cd /Applications/XAMPP/xamppfiles/htdocs/Wat_Financial    # macOS
# ຫຼື
cd C:\xampp\htdocs\Wat_Financial                           # Windows
```

### ຂັ້ນຕອນທີ 2 — ຕິດຕັ້ງ dependencies
```bash
npm install
```

### ຂັ້ນຕອນທີ 3A — Build ສຳລັບໃຊ້ງານ (Production)
```bash
npm run build
```
ຄຳສັ່ງນີ້ຈະສ້າງໄຟລ໌ `public/css/app.css` (minified).

### ຂັ້ນຕອນທີ 3B — Build ແລ Watch (Development)
```bash
npm run dev
```
ຄຳສັ່ງນີ້ຈະ rebuild CSS ໂດຍອັດຕະໂນມັດ ທຸກຄັ້ງທີ່ທ່ານແກ້ໄຂ HTML/PHP.  
ກົດ `Ctrl + C` ເພື່ອຢຸດ.

> **ໝາຍເຫດ:** ຖ້າ `public/css/app.css` ບໍ່ມີ ຫຼືຫວ່າງ ໜ້າເວັບຈະສະແດງໂດຍບໍ່ມີ style. ຕ້ອງ build ຢ່າງໜ້ອຍ 1 ຄັ້ງ.

---

## 8. ການເປີດໃຊ້ງານລະບົບ

1. ໃຫ້ XAMPP Apache ແລ MySQL ກຳລັງແລ່ນຢູ່
2. ໄຟລ໌ `public/css/app.css` ຕ້ອງ build ແລ້ວ (ຂໍ້ 7)
3. ເປີດ browser ໄປທີ່:

```
http://localhost/Wat_Financial
```

ລະບົບຈະ redirect ໄປໜ້າ Login ໂດຍອັດຕະໂນມັດ.

---

## 9. ການ Login ຄັ້ງທຳອິດ

ຂໍ້ມູນ login ຄ່າ default ທີ່ migration ສ້າງໃຫ້:

| ຂໍ້ມູນ | ຄ່າ |
|--------|-----|
| **Email** | `admin@watfinancial.local` |
| **Password** | `admin123` |

> **ສຳຄັນ:** ປ່ຽນ password ທັນທີຫຼັງ login ສຳເລັດ!  
> ໄປທີ່ **ຕັ້ງຄ່າ → ເປຕ່ຽນ Password**

---

## 10. ການຕັ້ງຄ່າ Apache (Virtual Host — ທາງເລືອກ)

ຖ້າຕ້ອງການ URL ສວຍງາມ ເຊັ່ນ `http://watfinancial.local` ແທນ `http://localhost/Wat_Financial`:

### macOS — ເພີ່ມ Virtual Host
1. ເປີດໄຟລ໌ `/Applications/XAMPP/xamppfiles/etc/extra/httpd-vhosts.conf`
2. ເພີ່ມ block ດັ່ງນີ້:

```apache
<VirtualHost *:80>
    ServerName watfinancial.local
    DocumentRoot "/Applications/XAMPP/xamppfiles/htdocs/Wat_Financial"
    <Directory "/Applications/XAMPP/xamppfiles/htdocs/Wat_Financial">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

3. ເພີ່ມ domain ໃນ `/etc/hosts`:
```
127.0.0.1   watfinancial.local
```

4. ຍ້ອນໃນ `config/config.php` ປ່ຽນ BASE_URL:
```php
define('BASE_URL', '');   // ຫວ່າງ ເພາະ Virtual Host ໃຊ້ root path
```

5. Restart Apache ຜ່ານ XAMPP Control Panel

---

## 11. ການແກ້ໄຂບັນຫາທົ່ວໄປ

### ບັນຫາ: ໜ້າໂລດ ແຕ່ບໍ່ມີ Style (ໜ້າຂາວ ບໍ່ສວຍ)
- **ສາເຫດ:** ຍັງບໍ່ໄດ້ Build TailwindCSS
- **ແກ້:** ລັນ `npm run build` ໃນ folder ໂປຣເຈັກ

---

### ບັນຫາ: `SQLSTATE[HY000] [1045] Access denied`
- **ສາເຫດ:** Username/Password MySQL ຜິດ
- **ແກ້:** ກວດ `DB_USER` ແລ `DB_PASS` ໃນ `config/config.php`

---

### ບັນຫາ: `Unknown database 'wat_financial'`
- **ສາເຫດ:** ຍັງບໍ່ໄດ້ລັນ schema.sql
- **ແກ້:** ກັບໄປທຳ [ຂໍ້ 5](#5-ການຕັ້ງຄ່າຖານຂໍ້ມູນ)

---

### ບັນຫາ: `Table 'wat_financial.settings' doesn't exist`
- **ສາເຫດ:** ລັນ `schema.sql` ແລ້ວ ແຕ່ຍັງບໍ່ໄດ້ລັນ `migrate_add_tables.sql`
- **ແກ້:** ລັນ migration file ໃນ phpMyAdmin (ຂໍ້ 5, ຂັ້ນຕອນທີ 3)

---

### ບັນຫາ: `404 Not Found` ສຳລັບທຸກ URL ນອກຈາກໜ້າ index
- **ສາເຫດ:** Apache `mod_rewrite` ຍັງບໍ່ enabled ຫຼື `.htaccess` ບໍ່ຖືກ
- **ແກ້:**
  1. ກວດ `AllowOverride All` ໃນ `httpd.conf`
  2. ໃຫ້ `mod_rewrite` enabled: ຫາ `#LoadModule rewrite_module` ໃນ `httpd.conf` ລຶບ `#` ອອກ
  3. Restart Apache

---

### ບັນຫາ: ໜ້າສະແດງ PHP code ດິບ (ບໍ່ execute)
- **ສາເຫດ:** ໄຟລ໌ຢູ່ນອກ `htdocs` ຫຼື Apache ບໍ່ໄດ້ handle `.php`
- **ແກ້:** ໃຫ້ folder ໂປຣເຈັກຢູ່ໃນ `htdocs/` ເທົ່ານັ້ນ

---

### ບັນຫາ: ຕົວໜັງສືລາວສະແດງເປັນ `????`
- **ສາເຫດ:** Charset ຖານຂໍ້ມູນບໍ່ແມ່ນ `utf8mb4`
- **ແກ້:** ໃຫ້ແນ່ໃຈວ່າ `database` ແລ tables ໃຊ້ `utf8mb4_unicode_ci`:
  ```sql
  ALTER DATABASE `wat_financial` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
  ```

---

## 12. ໂຄງສ້າງໄດເຣັກທໍຣີ

```
Wat_Financial/
├── app/
│   ├── controllers/        ← Logic handler ສຳລັບ request ແຕ່ລະໜ້າ
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── TransactionsController.php
│   │   ├── BudgetController.php
│   │   ├── GoalsController.php
│   │   ├── ReportsController.php
│   │   ├── RecurringController.php
│   │   ├── SettingsController.php
│   │   └── ...
│   ├── core/               ← ຫົວໃຈ MVC Framework
│   │   ├── App.php         ← Router: ແຈກຈ່າຍ URL ໄປ Controller
│   │   ├── Controller.php  ← Base Controller
│   │   └── Database.php    ← PDO Database Singleton
│   ├── models/             ← ຊັ້ນ Database Query
│   └── views/              ← HTML Templates (PHP)
│       ├── layouts/        ← Layout ຫຼັກ (nav, header, footer)
│       ├── dashboard/
│       ├── transactions/
│       ├── budget/
│       ├── goals/
│       ├── reports/
│       ├── settings/
│       └── auth/
├── config/
│   └── config.php          ← ການຕັ້ງຄ່າ DB ແລ App
├── database/
│   ├── schema.sql          ← Tables ຫຼັກ + Seed data
│   └── migrate_add_tables.sql  ← Tables ເພີ່ມເຕີມ
├── public/
│   ├── css/
│   │   └── app.css         ← TailwindCSS output (auto-generated)
│   └── img/
├── input.css               ← TailwindCSS source (input)
├── tailwind.config.js      ← TailwindCSS config
├── package.json            ← npm scripts (build/dev)
├── index.php               ← Front Controller (entry point)
├── DESIGN.md               ← Design System documentation
└── INSTALL.md              ← ໄຟລ໌ນີ້
```

---

## ສຸດທ້າຍ — Checklist ກ່ອນໃຊ້ງານ

- [ ] XAMPP Apache ກຳລັງແລ່ນ
- [ ] XAMPP MySQL ກຳລັງແລ່ນ
- [ ] ລັນ `database/schema.sql` ສຳເລັດ
- [ ] ລັນ `database/migrate_add_tables.sql` ສຳເລັດ
- [ ] `config/config.php` ຕັ້ງຄ່າ DB ຖືກຕ້ອງ
- [ ] ລັນ `npm install && npm run build` ສຳເລັດ
- [ ] ໄຟລ໌ `public/css/app.css` ມີຢູ່ ແລ ບໍ່ຫວ່າງ
- [ ] ເຂົ້າທີ່ `http://localhost/Wat_Financial` ໄດ້
- [ ] Login ດ້ວຍ `admin@watfinancial.local` / `admin123` ສຳເລັດ
- [ ] **ປ່ຽນ password admin ທັນທີ!**
