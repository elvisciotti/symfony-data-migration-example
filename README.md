# Symfony RESTful service sample

with swagger skeleton + two-db connection data migration + mongoDb connection

Symfony 4.3 RESTful api with code to migrate data (two Doctrine/MySQL connections + Command script using both), use endpoint (Swagger, Etag) and **MongoDB** connection, to use as a reference.

## Two connection and Data migration

*  Models under `src/Entity/V1` (old schema) and `src/Entity/V2` (new schema) 
* Console Command `\App\Command\MigrateV1ToV2Command` to migrate V1 -> V2 + interactive options to truncate data

## RESTful api

Two sample endpoints with *Swagger* config, Symfony native *serialization* with custom *groups* from URLs, *E-tags* for caching same content by hash

# MongoDB

    Bash into it:
    docker-compose up -d
    docker exec -it symfony-restful-api-data-migration-skeleton_db_mongo_1 bash

# Orchestration
*Docker* containers for the two db instances, and mongodb, for local usage, on ports 3310 and 3311

# How to run
On a machine with PHP 7.2 or more:

 *  start legacy db ("v1", on port 3310) and new database ("v2", on port 3310)
 
        docker-compose up -d
        
        (wait 20 seconds to be up)
    
 *  import given schema in into db v1

        docker-compose exec -T db_v1 mysql -u migrationapp -pmigrationapp migrationapp < /path/to/v1.sql
         
 * install libraries
 (composer should be installed in the system)
 
        composer install -o        
 * create db schema
 
        bin/console doctrine:schema:update --em=v2 --force
        
        # (see entities inside `src/Entity/V2/`)
        
        I could have used the migration bundle, safer for prod, but kept it simple for this test.
        
 * migrate data v1 into v2       
 
        bin/console app:migrate-v1-to-v2
        
        # (see code inside `src/Command/MigrateV1ToV2Command.php`)
    
 * Launch server
 
    [Install](https://symfony.com/download) `symfony` if missing
 
        symfony serve
     
Browse at at [http://127.0.0.1:8000/api/v2/doc](http://127.0.0.1:8000/api/v2/doc)

The documentation will appear

### Example of requests

Specific contact

    curl -s http://127.0.0.1:8000/api/v2/contacts/1?groups=contact
    
    {"name":"James Pappas","phone":"2 942 587 3408","email":null,"active":true}

All contacts

    curl -s http://127.0.0.1:8000/api/v2/contacts
    
    [
        {
            "id": 1,
            "name": "Name1",
            "phone": "11111",
            "email": null,
            "active": true
        },
        {
            "id": 2,
            "name": "Name2",
            "phone": "22222",
            "email": "******",
            "active": true
        },
        ...
    ]

All contacts - ID only

    http://127.0.0.1:8000/api/v2/contacts?groups=contact-id

    [
        {
            "id": 1
        },
        {
            "id": 2
        }
        ...
    ]

All contacts - with employment and organisation (serializing groups allowing customisation of returned subelements)

    curl -s http://127.0.0.1:8000/api/v2/contacts?groups[]=contact&groups[]=contact-employments&groups[]=employment&groups[]=employment-organisation&groups[]=organisation    
    
    [
        ...
        {
            "name": "******",
            "phone": "******",
            "email": null,
            "active": true,
            "employments": [
                {
                    "id": 9,
                    "organisation": {
                        "name": "******",
                        "address1": "******"
                    },
                    "title": "******"
                },
                {
                    "id": 12,
                    "organisation": {
                        "name": "******",
                        "address1": "******"
                    },
                    "title": "******"
                }
            ]
        },
        ...
    ]
    
