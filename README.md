## ToDo & Co
Todo application

## Installation

### 1. project's requirements

symfony cli, composer

### 2. clone the project
```
cd your_project_dir
git clone https://github.com/jonatanocr/p8_todoList.git
```
### 3. Configuration and dependencies
```
cd p8_todoList
# edit .env.exemple to .env file to match your configuration

# make Composer install the project's dependencies into vendor/
composer install
```

### 4. Set the database
```
# Create the database :
php bin/console doctrine:database:create
# Add database schema: 
php bin/console doctrine:migrations:migrate
# You can load the fixtures (10 users are already created to test the app. Test with user1@gmail.com and 'password' as password)
php bin/console doctrine:fixtures:load
```

### 5. Yarn
```
yarn install --force
yarn encore production
```

### 6. Test it
```
symfony server:start
```
Open your browser and navigate to http://localhost:8000/

## License
[MIT](https://choosealicense.com/licenses/mit/)