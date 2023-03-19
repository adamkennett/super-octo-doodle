### An example API task I was asked to do in Laravel

* For a Movie resource, Create, Read, Update, Delete
* Year and Genre relationships
* Migrations and seed data
* Validation on request classes
* Fetch from a third party API
* Integration tests for the endpoints


##### Not required 
* Auth
* Frontend

##### Msc notes
I've used a rough interpretation of the service pattern, e.g, the services can be used in both the API controllers and the resource controllers.

I didnt use a repository pattern because in my opinion Laravel is tied so tightly to Eloquent it confuses things, in large apps this might be an issue but for something  this simple probably not and time constraints meant this wasnt pratical.

##### Given more time I'd have
* Error handling, this all largely presumes things are going to follow the happy path
* Added authentication and authorisation for the resources using gates or policies
* Created a simple frontend, the Inertia scaffolding is there 
* Written some unit test (although the integration tests would catch most issues I think)
* Added some more realistic seed data, the external API uses movie titles to get rating, and lorium ipsum isnt a movie title.
* Run this through a tool like Pint to standardise the formatting 

##### Main take away from this task 
* I need to write unit tests as I go alongside the integration tests it is a weak point.
