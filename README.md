# 🛠️ Multi-Language Token Authentication API  

This project is the same backend API built **three times** — in **PHP**, **Python**, and **Go**.  
The goal is to learn backend engineering across different languages while keeping the design consistent.  

It focuses on one of the most important backend concepts:  
👉 **secure login and token-based authentication**.  

---

## 🔑 What It Does  

- Users can **log in with a username & password**  
- On login, the system provides:  
  - a **short-lived access token** (valid for 1 hour)  
  - a **refresh token** (stored safely in a cookie)  
- The **access token** is used for protected API routes  
- When it expires, you can **refresh** with the cookie to get a new one  
- All three languages (PHP, Python, Go) use the **same structure** so you can compare them side by side  

---

## 🚀 Tech Stack  

| Language | Framework/Library            | Database |
|----------|------------------------------|-----------|
| PHP      | Slim Framework + firebase/php-jwt | SQLite |
| Python   | Standard libraries (minimal) | SQLite |
| Go       | Standard libraries (minimal) | SQLite |

---

## 📂 API Overview  

- **Login** → `POST /api/v1/auth/login`  
  - Validates user credentials  
  - Returns access token + sets refresh cookie  

- **Refresh Token** → `POST /api/v1/auth/refresh`  
  - Reads refresh cookie  
  - Returns a new access token + new refresh cookie  

- **Protected Routes**  
  - Can only be accessed with a valid access token  

---

## 🛡️ Security Basics  

- Refresh tokens stored in **secure, HttpOnly cookies**  
- Tokens are **rotated** (new one on every refresh)  
- Passwords stored using **strong hashing**  

---

## 🌱 Future Additions  

- Logout & token blacklisting  
- Role-based access control (e.g., admin vs. user)  
- Support for **PASETO** tokens  
- Use PostgreSQL or MySQL for production setups  

---

## 📊 Authentication Flow  

```mermaid
flowchart TD
    A[User Login Request] --> B[Validate Username & Password]
    B -->|Valid| C[Issue Access Token + Refresh Cookie]
    B -->|Invalid| Z[Return Error]

    C --> D[Access Protected API]
    D -->|Token Valid| E[Allow Access]
    D -->|Token Expired| F[Use Refresh Cookie]

    F --> G[Issue New Access Token + New Refresh Cookie]
    G --> D
