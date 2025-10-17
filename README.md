# Ifgram

Instagram + Telegram ijtimoiy tarmoq (PHP/SQLite MVP)

## Setup

1. PHP server ishga tushiring:
```bash
php -S localhost:8000
```

2. Brauzerda oching: http://localhost:8000

## API Endpoints

### Auth
- POST `/api/auth.php?action=register` - Ro'yxatdan o'tish
- POST `/api/auth.php?action=login` - Kirish

### Posts
- POST `/api/posts.php?action=create` - Post yaratish
- GET `/api/posts.php?action=feed&user_id=X` - Feed olish
- POST `/api/posts.php?action=like` - Like qo'yish/olib tashlash
- POST `/api/posts.php?action=comment` - Izoh qo'shish
- GET `/api/posts.php?action=comments&post_id=X` - Izohlarni olish

### Users
- GET `/api/users.php?action=profile&user_id=X` - Profil ma'lumotlari
- POST `/api/users.php?action=follow` - Follow/Unfollow
- GET `/api/users.php?action=search&q=query` - Foydalanuvchi qidirish
- GET `/api/users.php?action=posts&user_id=X` - Foydalanuvchi postlari

### Stories
- POST `/api/stories.php?action=create` - Story yaratish
- GET `/api/stories.php?action=feed&user_id=X` - Aktiv storylar
- POST `/api/stories.php?action=view` - Story ko'rish

## Xususiyatlar

- ✅ Ro'yxatdan o'tish/Kirish
- ✅ Instagram-style dizayn
- ✅ Post yaratish (hashtag qo'llab-quvvatlash)
- ✅ Feed ko'rish
- ✅ Like/Unlike
- ✅ Izohlar tizimi
- ✅ Stories (24 soatlik)
- ✅ Follow/Unfollow
- ✅ Profil sahifasi
- ✅ Qidiruv
- ✅ Real-time like/comment counts
- ✅ SQLite database
