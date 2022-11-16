# SportNinja Mini Stats API Assignment Challenge
```
    Build a Laravel project with a simple HTTP API to persist and retrieve individual player stats in a
leaderboard format. Keep in mind that performance of stat persistence and retrieval are key
aspects for this project.
```
## Please follow the next steps to be able to run the project in your local environment
- Create your database
- Assign all credentials and values to your .env file (include this line REDIS_CLIENT=predis)

### Execute the following commands
- composer install 
- composer dumpautoload
- php artisan key:generate
- php artisan migrate --seed 

#### Notes
```
I tested my indexes and queries with the command sql explain in order to try to improve the query execution 
Postman: You can use this link to donwload the collection in postman to be able to test both endpoints 
         https://www.postman.com/collections/4911016f174ac66e3170
 
I did many comments in the code trying to explain why I wrote each code in that way. Please take a look to that
Thanks :)
```

