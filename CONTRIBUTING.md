# Contributing ToDo & Co
This application is written with [Symfony 6.2](https://symfony.com/doc/current/index.html)
When contributing to this repository, please follow the steps bellow.

## 1. Consult the issues already created on the backlog
Check in the backlog if the issue is not already created. If not, create an issue and describe the problem or the new feature.

## 2. Create a new branch
Create a new branch with `git checkout -b <branch-name>` command.

## 3. Code
Write your code.

Your code has to respect PHP PSR standards and Symfony best practices.
Write tests to your new code.

## 4. Test the application
To test your application, you can load the fixtures `php bin/console doctrine:fixtures:load`

When your done with your development, run the test with `php bin/phpunit` command.

You can generate a code coverage report with `php bin/phpunit --coverage-html public/test-coverage`.

The project code coverage must be at least at 70%.
To read more about Symfony testing : [Official documentation](https://symfony.com/doc/current/testing.html)

## 5. Documentation
If you make some core changes, update the README.md or other documentation like app diagrams with details of your changes.

## 6. Pull Request Process
Push your code on the github repository `git push -u origin <branch-name>`

Create a new  [pull request](https://docs.github.com/en/pull-requests/collaborating-with-pull-requests/proposing-changes-to-your-work-with-pull-requests/about-pull-requests).

Your code will be review by another developer. Once you recevied your approval, you can merge your Pull request to the main branch.
 

