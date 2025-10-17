# Ifgram

Instagram + Telegram ijtimoiy tarmoq (PHP/SQLite MVP)

## Setup

1. PHP server ishga tushiring:
```bash
php -S localhost:8000
```

2. Brauzerda oching: http://localhost:8000

## API Endpoints

- POST `/api/auth.php?action=register` - Ro'yxatdan o'tish
- POST `/api/auth.php?action=login` - Kirish
- POST `/api/posts.php?action=create` - Post yaratish
- GET `/api/posts.php?action=feed&user_id=X` - Feed olish
- POST `/api/posts.php?action=like` - Like qo'yish

## Xususiyatlar

- ✅ Ro'yxatdan o'tish/Kirish
- ✅ Post yaratish
- ✅ Feed ko'rish
- ✅ Like qo'yish
- ✅ SQLite database
