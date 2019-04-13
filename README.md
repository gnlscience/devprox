# devprox
## Installation Steps
1. Clone repository
2. cd devprox
3. docker-compose build
4. docker-compose up -d
5. docker exec -it sf4_php bash
6. cd sf4
7. composer install
8. php bin/console doctrine:migrations:migrate
9. exit docker container
10. mkdir public/uploads
10. chmod -R 755 public/uploads

## Test 1
### Form
http:/localhost/test1
### List Form Submitions
http:/localhost/test1/results

## Test2
### Download CSV
http:/localhost/test2
### Upload CSV
http:/localhost/test2/import






