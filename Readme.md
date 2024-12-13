# PHP Development Environment with Docker

This project sets up a PHP development environment using Docker, including services for PHP, Nginx, and MySQL. The setup includes a working Nginx web server, a PHP-FPM backend, and a MySQL database, all orchestrated using Docker Compose.

## Prerequisites

Before you begin, ensure you have the following installed:

- Docker
- Docker Compose

## Project Structure

```bash
/project_root
├── docker-compose.yml     # Docker Compose configuration file
├── Dockerfile             # Dockerfile for building PHP container (optional)
├── .env                   # Environment variables (for MySQL credentials)
├── src/                   # Source code for your PHP application
├── nginx/
│   └── default.conf       # Nginx configuration file
└── mysql/
    └── init.sql           # MySQL initialization scripts (optional)

```

## Getting Started

### Step 1: Clone the Repository

Clone the project to your local machine.

```bash
git clone https://github.com/ogeeDeveloper/Applied-Web-Assignment-2024.git
cd Applied-Web-Assignment-2024

```

### Step 2: Create `.env` File

Create a `.env` file in the root directory to store your environment variables, such as database credentials.

**Example `.env` file**:
```bash
MYSQL_ROOT_PASSWORD=rootpassword123
MYSQL_DATABASE=my_database
MYSQL_USER=myuser
MYSQL_PASSWORD=mypassword123
```

### Step 3: Build and Run the Containers

Run the following command to build and start your Docker containers:

```bash
docker-compose up --build

```

This will start the following services:

- **PHP**: Using PHP 8.2 with PHP-FPM.
- **Nginx**: Web server running on port `8080`.
- **MySQL**: Database running on port `3306`.

### Step 4: Access the Application

Once the containers are up and running, you can access the application by visiting:
```http://localhost:8080```

### Step 5: Stopping the Containers

To stop the containers, use:
```bash
docker-compose down
```

This will stop and remove all running containers.

## Customizing the Project

### Adding PHP Extensions

If you need to add PHP extensions or customize the PHP environment, you can modify the `Dockerfile`.

**Example Dockerfile**:
```Dockerfile
FROM php:8.2-fpm

# Install required PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY ./src /var/www/html
```

### MySQL Initialization Scripts

Place any MySQL initialization scripts (such as schema creation or seed data) in the `mysql/` directory. These scripts will be executed when the MySQL container is first initialized.

### Nginx Configuration

Nginx is pre-configured to serve PHP applications via PHP-FPM. You can modify the `nginx/default.conf` file to customize the web server configuration.

**Example Nginx Configuration**:
```nginx
server {
    listen 80;
    server_name localhost;

    root /var/www/html;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php$is_args$args;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass php:9000;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

## Environment Variables

The following environment variables are used for configuring the MySQL service:

- `MYSQL_ROOT_PASSWORD`: The password for the MySQL root user.
- `MYSQL_DATABASE`: The name of the database to create on container initialization.
- `MYSQL_USER`: The name of the non-root MySQL user (optional).
- `MYSQL_PASSWORD`: The password for the non-root MySQL user (optional).

## Troubleshooting

- **Error: `MYSQL_USER="root" cannot be used`**: You should not set `MYSQL_USER="root"`. Use `MYSQL_ROOT_PASSWORD` to set the root password instead. If you want to create a separate user, use `MYSQL_USER` and `MYSQL_PASSWORD` for a non-root user.
    
- **Database Initialization**: Ensure that the SQL scripts in `mysql/` directory are named correctly and are valid SQL statements.
    

## Useful Commands

- **Rebuild Containers**:
  ```bash
  docker-compose up --build
  ```
- **Stop Containers**:
  ```bash docker-compose down ```

- **Access PHP Container**:
  ```bash docker-compose exec php bash```

- **Access MySQL Container**:
  ```bash docker-compose exec mysql bash```

## Future Enhancements

- Adding a frontend for user interaction.
- Implementing database migrations with tools like Flyway or Liquibase.
- Using Docker secrets for more secure password management in production.

## If you want to manually run database migrations run the below command
```
docker-compose exec php php /var/www/public/run-migrations.php
```

## If you don't want to lose your existing data, you can manually run the migration file
```
docker-compose exec mysql mysql -u$MYSQL_USER -p$MYSQL_PASSWORD $MYSQL_DATABASE -e "source /docker-entrypoint-initdb.d/V*__migration_name.sql"
```