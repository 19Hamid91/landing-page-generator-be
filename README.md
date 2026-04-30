# AI Sales Page Generator - Backend

This is the backend API for the AI Sales Page Generator, built with Laravel 11. It handles user authentication, data management for sales pages, and integration with the Google Gemini AI for content generation.

## Features

- **Authentication**: Secure API authentication using Laravel Sanctum.
- **Sales Page Management**: Full CRUD operations for managing generated sales pages.
- **AI Integration**: Powered by Google Gemini AI to generate high-converting sales copy.
- **JSON Based**: Structured data storage for flexible template rendering.

## Tech Stack

- **Framework**: Laravel 11
- **Database**: MySQL / MariaDB
- **Authentication**: Laravel Sanctum
- **AI Service**: Google Gemini API

## Requirements

- PHP >= 8.2
- Composer
- MySQL / MariaDB

## Installation

1. **Clone the repository** and navigate to the `be` directory.
2. **Install dependencies**:
   ```bash
   composer install
   ```
3. **Setup environment**:
   ```bash
   cp .env.example .env
   ```
4. **Configure Database**: Update `.env` with your database credentials.
5. **Configure Gemini AI**: Add your Google Gemini API key in `.env`:
   ```env
   GEMINI_API_KEY=your_api_key_here
   ```
6. **Generate application key**:
   ```bash
   php artisan key:generate
   ```
7. **Run migrations**:
   ```bash
   php artisan migrate
   ```
8. **Start the server**:
   ```bash
   php artisan serve
   ```

## API Routes

- `POST /api/auth/register` - User registration
- `POST /api/auth/login` - User login
- `POST /api/auth/logout` - User logout (Protected)
- `GET /api/user` - Get current user profile (Protected)
- `GET /api/sales-pages` - List all sales pages (Protected)
- `POST /api/sales-pages` - Create/Generate new sales page (Protected)
- `GET /api/sales-pages/{id}` - Show sales page details (Protected)
- `PUT /api/sales-pages/{id}` - Update sales page (Protected)
- `DELETE /api/sales-pages/{id}` - Delete sales page (Protected)
