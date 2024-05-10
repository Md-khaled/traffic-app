## Installation
1. Clone the repository: `git clone https://github.com/Md-khaled/traffic-app.git`
2. Navigate to the project directory: `cd traffic-app`
3. Install Composer dependencies: `composer install`
4. Copy the .env.example file to .env: `cp .env.example .env`
5. Configure your environment variables like database connection in .env file and mail like mailtrap
6. Generate a new application key: `php artisan key:generate`
7. Run database migrations: `php artisan migrate:fresh --seed`
8. Set api domain in .env file API_DOMAIN=''
10. Serve the application: `php artisan serve`

The application will be available at http://your_domain.