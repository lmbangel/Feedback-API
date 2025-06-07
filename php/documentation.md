feedback-api-php/
│
├── public/
│   └── index.php         ← Entry point
│
├── src/
│   ├── routes.php        ← Route definitions
│   ├── middleware.php    ← JWT + rate limit middleware
│   └── db.php            ← SQLite connection
│
├── .env                  ← For config (optional)
├── database.sqlite       ← SQLite DB file
├── composer.json
