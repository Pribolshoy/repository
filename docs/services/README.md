### Services:

Services are devided into a few groups. Each of them serve to certain purposes.

##### Abstract service

It is a base service. It don't use external cache storages like redis and so.   
Being an singleton it keep items in RAM until php process die.

##### Abstract cacheble service

It is the next level and its name speaks for itself - it use cache storage.  
It more rich in methods then its parent.   
Idea of this service type in that it must be used by tables that contain not more than 1000 rows.  
Because it fetch all table rows into itself from cache storage or database and it isn't good idea to keep in RAM thousands rows.

##### Enormous cacheble service

In most cases tables contains at least of thousands rows and we need service for such cases.  
Idea of enormous service is that we inserts all needed table rows in cache storage.  
But unlike __Abstract cacheble service__ it not keep all of them in itself (RAM).  
It selects items from cache storage accurately, by ID for example.

##### Paginated cacheble service

The last one. It more specific service than other.  
It inserts in cache repository not items, but pagination information.   
Instead of ID it use hash of params for selecting items from database.   