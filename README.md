# Brain Storm

<br>

Now, we will start with the Authentication.
> note: this app is RESTful Api based, so we are using `Sanctum` Authentication.

You can see the Authentication controller <a href="file://app/Http/Controllers/Api/AuthController.php">here</a>

For the other user info like preferences or profile image or others, I suggest creating a profile model, 
so each user instance would have one profile instance that contains other information about the user like a profile image or preferences, keeping
the important info seperated from other info, by fetching a `ProfileResource` api resource instance on retreiving user info. 

checkout the DB scheme!
