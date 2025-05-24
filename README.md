# Content Scheduling Application

A Laravel-based application for scheduling and managing social media posts across multiple platforms.

## Features

- User authentication with Laravel Sanctum
- Create, read, update, and delete posts
- Schedule posts for future publication
- Support for multiple social media platforms
- Image upload support
- Platform-specific validation
- Daily post limit (10 posts per day)
- Automatic post processing
- Post analytics

## Requirements

- PHP 8.1 or higher
- Composer
- MySQL/PostgreSQL/SQLite
- Node.js and NPM (for frontend assets)

## Installation

1. Clone the repository:
```bash
git clone <repository-url>
cd <project-directory>
```

2. Install PHP dependencies:
```bash
composer install
```

3. Copy the environment file:
```bash
cp .env.example .env
```

4. Generate application key:
```bash
php artisan key:generate
```

5. Configure your database in the `.env` file:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

6. Run migrations and seeders:
```bash
php artisan migrate --seed
```

7. Create storage link for public files:
```bash
php artisan storage:link
```

8. Start the development server:
```bash
php artisan serve
```

9. Start the scheduler (in a separate terminal):
```bash
php artisan schedule:work
```

## API Endpoints

### Authentication
- POST /api/register - Register a new user
- POST /api/login - Login user
- POST /api/logout - Logout user (requires authentication)
- GET /api/profile - Get user profile (requires authentication)

### Posts
- GET /api/posts - List all posts (requires authentication)
- POST /api/posts - Create a new post (requires authentication)
- GET /api/posts/{id} - Get a specific post (requires authentication)
- PUT /api/posts/{id} - Update a post (requires authentication)
- DELETE /api/posts/{id} - Delete a post (requires authentication)

### Platforms
- GET /api/platforms - List all available platforms (requires authentication)
- POST /api/platforms/{id}/toggle - Toggle platform status (requires authentication)

## Post Processing

The application includes a command to process scheduled posts:

```bash
php artisan posts:process-scheduled
```

This command is automatically scheduled to run every minute when using the scheduler.

## Security

- All API endpoints (except login and register) require authentication
- Posts can only be modified by their owners
- Published posts cannot be modified or deleted
- Rate limiting is implemented for post creation
- File upload validation is in place

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a new Pull Request
