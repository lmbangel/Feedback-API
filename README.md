🛠️ Multi-Language Backend API (PHP • Python • Go)
This project is a backend API implemented in three languages—PHP, Python, and Go—to build cross-language backend engineering skills. The core goal is to learn and master authentication flows, secure token handling, and RESTful design.

🔐 Features
Secure login with password hashing

JWT-based access tokens (1-hour expiry)

Refresh token implementation with secure cookies

Middleware for protecting routes

SQLite as a lightweight backend DB

API versioning structure (/api/v1/...)

Consistent design replicated in PHP, Python, and Go

🚀 Technologies
Language	Framework	Auth Library	DB
PHP	Slim Framework	firebase/php-jwt	SQLite
Python                              	SQLite
Go                                  	SQLite

🧪 Authentication Flow
Login (POST /api/v1/auth/login)

Validates credentials

Returns JWT (in body) and sets Refresh Token in an HttpOnly cookie

Token Refresh (POST /api/v1/refresh)

Reads the refresh token from cookie

Issues a new JWT and refresh token

Protected Endpoints

Use middleware to validate JWT

🛡️ Security Notes
Refresh token is stored as HttpOnly, Secure, SameSite=Strict

Tokens are rotated on each refresh (single-use refresh pattern)

Passwords stored with password_hash and verified via password_verify (PHP) or equivalents

🔄 Future Improvements
Token revocation / blacklisting

Role-based access control (RBAC)

Optional PASETO support for more secure token handling

Support for PostgreSQL or MySQL in production setups