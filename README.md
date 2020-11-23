# Laravel Json-api XYZ Cookbook

These are my notes I wrote learning how to setup CloudCreatity Json-api 3.1.0 in Laravel 8.15.0. I'm trying something new. I'm writing this tutorial as a git project. Each commit is a step of the tutorial.

I am not fluent in PHP, Laravel, or Json-api. Please leave a comment (or issue) if you find an error. These notes were based off of what I read from these webpages.

- https://howtojsonapi.com/laravel.html
- https://laravel-json-api.readthedocs.io/en/latest/installation/

These notes assume you're using Homestead (so the mysql command never specifies a user). If you need help with Homestead you need to look elsewhere. 

## Homestead

In your Homestead.yaml file make sure you have something like the following.

>     folders:
>        - map: ~/code
>          to: /home/vagrant/code
>    
>    sites:
>        - map: xyz.test
>          to: /home/vagrant/code/xyz/public

If your vagrant VM was already running have to reprovision to re-read the config file. Run this from within your Homestead directory.

    vagrant provision

And in /etc/hosts you need the following also.

>    192.168.10.10 xyz.test

192.168.10.10 is the typical IP for the Homestead VM.

## New Laravel project

    cd ~/Homestead
    vagrant ssh
    cd laravel/
    laravel new xyz

If you are using Homestead you should be able to point your browser to http://xyz.test and you'll see the defautl Laravel start page.

These are all the commands to install and get everything setup that will be used later on.

    cd xyz
    composer require cloudcreativity/laravel-json-api
    composer require --dev "cloudcreativity/json-api-testing"
    php artisan make:json-api
    php artisan make:model X --migration
    php artisan make:model Y --migration
    php artisan make:model Z --migration
    php artisan make:migration create_x_z_table
    php artisan make:json-api:schema X
    php artisan make:json-api:schema Y
    php artisan make:json-api:schema Z
    php artisan make:json-api:adapter X
    php artisan make:json-api:adapter Y
    php artisan make:json-api:adapter Z
    php artisan make:seeder XYZSeeder

[View the project at this stage.](https://github.com/magnusviri/laravel_jsonapi_xyz/tree/53fb9f1caed36a1fea7f249a276ce77e75586619)

## RouteServiceProvider

In app/Providers/RouteServiceProvider.php change:

>    $this->routes(function () {
>        Route::prefix('api')
>            ->middleware('api')
>            ->namespace($this->namespace)
>            ->group(base_path('routes/api.php'));

to

>    Route::middleware('api')
>        ->namespace($this->namespace)
>        ->group(base_path('routes/api.php'));

[View changes on github.](
https://github.com/magnusviri/laravel_jsonapi_xyz/commit/c96a5e1ec888fd25a10032341c4fc8669e99b619)

## Database Migrations

In this example, X has many Y's and Z's, Y will have only one X, but Z will have many X's.

Note, the migration is creating tables named x_e_s, y_s, and z_s. These names suck, but because this is part of laravel's automatic plurization of table names, I'm leaving it as is so that the all of the plurization stuff just works. If you change the names you have to then add more code helping Laravel know what the plural names are.

In database/migrations/2020_11_23_222908_create_x_e_s_table.php in create() add:

>    Schema::create('x_e_s', function (Blueprint $table) {
>      $table->id();
>      $table->timestamps();
>      $table->string('name');
>    });

In database/migrations/2020_11_23_222908_create_y_s_table.php in create() add:

>    Schema::create('y_s', function (Blueprint $table) {
>      $table->id();
>      $table->timestamps();
>      $table->string('name');
>      $table->bigInteger('x_id')->unsigned()->index();
>    });

In database/migrations/2020_11_23_222908_create_z_s_table.php in create() add:

>    Schema::create('z_s', function (Blueprint $table) {
>      $table->id();
>      $table->timestamps();
>      $table->string('name');
>    });

In database/migrations/2020_11_04_222908_create_x_z_table.php in create() add:

>    Schema::create('x_z', function (Blueprint $table) {
>      // $table->id();
>      // $table->timestamps();
>      $table->bigInteger('x_id')->unsigned()->index();
>      $table->foreign('x_id')->references('id')->on('x_e_s');
>      $table->bigInteger('z_id')->unsigned()->index();
>      $table->foreign('z_id')->references('id')->on('z_s');
>    });

[View changes on github.](
https://github.com/magnusviri/laravel_jsonapi_xyz/commit/5509821c35c5cf52c3498d6c151ceec0e6f0dc4f)

    mysql
    create database xyz;
    exit

This creates the xyz database.

    php artisan migrate

If you get errors, make sure .env has the correct password for mysql. If you're using Homestead, the user is root and the password is secret (unless you changed them).

[View Mysql settings on github.](
https://github.com/magnusviri/laravel_jsonapi_xyz/commit/918f68170c4cc8975fb3b67cb604ab5429a5428c)

If you get an error and the migrations don't complete, you need to undo the migrations and fix the errors then redo the migration. This will undo the migrations.

    php artisan migrate:rollback

I've noticed sometimes rolling back all the way doesn't work. If this is the case you'll see  an error that a table already exists when you run the migrate command. You have to connect to mysql and delete the table manually.

    mysql
    use xyz;
    drop table x_z;
    exit
    php artisan migrate

## Configure Models

In app/Models/X.php add something like this:

>    class X extends Model
>    {
>        use HasFactory;
>    
>        protected $fillable = ['name'];
>    
>        public function ys()
>        {
>            return $this->hasMany('App\Models\Y');
>        }
>        public function zs()
>        {
>            return $this->belongsToMany('App\Models\Z');
>        }
>    }

In app/Models/Y.php add something like this:

>    class Y extends Model
>    {
>        use HasFactory;
>    
>        protected $fillable = ['name', 'x_id'];
>    
>        public function x()
>        {
>            return $this->belongsTo('App\Models\X');
>        }
>    }

In app/Models/Z.php add something like this:

>    class Z extends Model
>    {
>        use HasFactory;
>    
>        protected $fillable = ['name'];
>    
>        public function xes()
>        {
>            return $this->belongsToMany('App\Models\X');
>        }
>    }

Instead of specifying $fillable, you could also go the oposite and specify $guarded, like the following.

>    protected $guarded = [];

[View changes on github.](
https://github.com/magnusviri/laravel_jsonapi_xyz/commit/b17a9902a4b7d703fdeb9f3c541b06fefb722928)

## Json-Api Schema

In app/JsonApi/XES/Schema.php change the following

>    protected $resourceType = 'x-e-s';

To 

>    protected $resourceType = 'xes';

I don't know why the above change was needed. It didn't work unless I made the change.

Also change the following

>    public function getAttributes($resource)
>    {
>        return [
>            'createdAt' => $resource->created_at,
>            'updatedAt' => $resource->updated_at,
>        ];
>    }

to this

>    public function getAttributes($resource)
>    {
>        return [
>            'createdAt' => $resource->created_at,
>            'updatedAt' => $resource->updated_at,
>            'name' => $resource->name,
>        ];
>    }
>    
>    public function getRelationships($resource, $isPrimary, array $includeRelationships)
>    {
>        return [
>            'ys' => [
>                self::SHOW_SELF => true,
>                self::SHOW_RELATED => true,
>            ]
>            'zs' => [
>                self::SHOW_SELF => true,
>                self::SHOW_RELATED => true,
>            ]
>        ];
>    }

In app/JsonApi/YS/Schema.php change the following

>    protected $resourceType = 'y-s';

to 

>    protected $resourceType = 'ys';

In app/JsonApi/ZS/Schema.php change the following

>    protected $resourceType = 'z-s';

to 

>    protected $resourceType = 'zs';

In app/JsonApi/YS/Schema.php and app/JsonApi/ZS/Schema.php change the following

>     public function getAttributes($resource)
>     {
>         return [
>             'createdAt' => $resource->created_at,
>             'updatedAt' => $resource->updated_at,
>             'name' => $resource->name,
>         ];
>     }
>     
>     public function getRelationships($resource, $isPrimary, array $includeRelationships)
>     {
>         return [
>             'xes' => [
>                 self::SHOW_SELF => true,
>                 self::SHOW_RELATED => true,
>             ]
>         ];
>     }

[View changes on github.](
https://github.com/magnusviri/laravel_jsonapi_xyz/commit/cd17c6e31a1372c818e6b7ce888a4cf0c2818047)

## Json-Api Adapters

In app/JsonApi/XES/Adapter.php add "Models" like this.

>    public function __construct(StandardStrategy $paging)
>    {
>        parent::__construct(new \App\X(), $paging);
>    }

to

>    public function __construct(StandardStrategy $paging)
>    {
>        parent::__construct(new \App\Models\X(), $paging);
>    }

Add "Models" the same way to app/JsonApi/YS/Adapter.php and app/JsonApi/ZS/Adapter.php.

At the bottom of app/JsonApi/XES/Adapter.php (within the Adapter class) add:

>    protected function zs()
>    {
>        return $this->hasMany();
>    }

At the bottom of app/JsonApi/YS/Adapter.php and app/JsonApi/ZS/Adapter.php (within the Adapter class) add:

>    protected function xes()
>    {
>        return $this->hasMany();
>    }

[View changes on github.](
https://github.com/magnusviri/laravel_jsonapi_xyz/commit/757635ee6741f0686360b473005a90011affba29)

## Json-Api config

In config/json-api-default.php change the following

>    'resources' => [
>        'posts' => \App\Post::class,
>    ],

to this

>    'resources' => [
>        'xes' => \App\Models\X::class,
>        'ys' => \App\Models\Y::class,
>        'zs' => \App\Models\Z::class,
>    ],

[View changes on github.](
https://github.com/magnusviri/laravel_jsonapi_xyz/commit/ecec4f7d4866820aa5cce880ef1c95cf2abaa92e)

## Routes

In routes/api.php add the following to the bottom.

>    JsonApi::register('default')->routes(function ($api) {
>        $api->resource('xes')->relationships(function ($relations) {
>            $relations->hasMany('ys');
>            $relations->hasMany('zs');
>        });
>        $api->resource('ys')->relationships(function ($relations) {
>            $relations->hasMany('xes');
>        });
>        $api->resource('zs')->relationships(function ($relations) {
>            $relations->hasMany('xes');
>        });
>    });

[View changes on github.](
https://github.com/magnusviri/laravel_jsonapi_xyz/commit/241d00314e227164fcd6cfcfa9f05f0ce5b66174)

## Seeder

In database/DatabaseSeeder.php add the following.

>    public function run()
>    {
>        // \App\Models\User::factory(10)->create();
>        $this->call(XYZSeeder::class);
>    }

In database/XYZSeeder.php change the file to the following.

>    <?php
>    
>    namespace Database\Seeders;
>    use App\Models\X;
>    
>    use Illuminate\Database\Seeder;
>    
>    class XYZSeeder extends Seeder
>    {
>        /**
>         * Run the database seeds.
>         *
>         * @return void
>         */
>        public function run()
>        {
>            $x1 = X::create(['name' => 'x1']);
>    
>            $x1->ys()->createMany([
>                ['name' => 'x1y1'],
>                ['name' => 'x1y2'],
>            ]);
>    
>            $x1->zs()->createMany([
>                ['name' => 'x1z1'],
>                ['name' => 'x1z2'],
>            ]);
>    
>            $x2 = X::create(['name' => 'x2']);
>    
>            $x2->ys()->createMany([
>                ['name' => 'x2y1'],
>                ['name' => 'x2y2'],
>            ]);
>    
>            $x2->zs()->createMany([
>                ['name' => 'x2z1'],
>                ['name' => 'x2z2'],
>            ]);
>        }
>    }

[View changes on github.](
https://github.com/magnusviri/laravel_jsonapi_xyz/commit/4291f894becd6d89b6ae2f0cd494f0fbd7a340df)

    php artisan db:seed

To start everything over run this.

    php artisan migrate:fresh --seed

## Results

    curl http://xyz.test/api/v1/xes

>    {
>        "data": [
>            {
>                "attributes": {
>                    "createdAt": "2020-11-23T19:16:56.000000Z",
>                    "name": "x1",
>                    "updatedAt": "2020-11-23T19:16:56.000000Z"
>                },
>                "id": "1",
>                "links": {
>                    "self": "http://xyz.test/api/v1/xes/1"
>                },
>                "relationships": {
>                    "ys": {
>                        "links": {
>                            "related": "http://xyz.test/api/v1/xes/1/ys",
>                            "self": "http://xyz.test/api/v1/xes/1/relationships/ys"
>                        }
>                    },
>                    "zs": {
>                        "links": {
>                            "related": "http://xyz.test/api/v1/xes/1/zs",
>                            "self": "http://xyz.test/api/v1/xes/1/relationships/zs"
>                        }
>                    }
>                },
>                "type": "xes"
>            },
>            {
>                "attributes": {
>                    "createdAt": "2020-11-23T19:16:56.000000Z",
>                    "name": "x2",
>                    "updatedAt": "2020-11-23T19:16:56.000000Z"
>                },
>                "id": "2",
>                "links": {
>                    "self": "http://xyz.test/api/v1/xes/2"
>                },
>                "relationships": {
>                    "ys": {
>                        "links": {
>                            "related": "http://xyz.test/api/v1/xes/2/ys",
>                            "self": "http://xyz.test/api/v1/xes/2/relationships/ys"
>                        }
>                    },
>                    "zs": {
>                        "links": {
>                            "related": "http://xyz.test/api/v1/xes/2/zs",
>                            "self": "http://xyz.test/api/v1/xes/2/relationships/zs"
>                        }
>                    }
>                },
>                "type": "xes"
>            }
>        ]
>    }

    curl http://xyz.test/api/v1/ys

>    {
>        "data": [
>            {
>                "attributes": {
>                    "createdAt": "2020-11-23T19:16:56.000000Z",
>                    "name": "x1y1",
>                    "updatedAt": "2020-11-23T19:16:56.000000Z"
>                },
>                "id": "1",
>                "links": {
>                    "self": "http://xyz.test/api/v1/ys/1"
>                },
>                "relationships": {
>                    "xes": {
>                        "links": {
>                            "related": "http://xyz.test/api/v1/ys/1/xes",
>                            "self": "http://xyz.test/api/v1/ys/1/relationships/xes"
>                        }
>                    }
>                },
>                "type": "ys"
>            },
>            {
>                "attributes": {
>                    "createdAt": "2020-11-23T19:16:56.000000Z",
>                    "name": "x1y2",
>                    "updatedAt": "2020-11-23T19:16:56.000000Z"
>                },
>                "id": "2",
>                "links": {
>                    "self": "http://xyz.test/api/v1/ys/2"
>                },
>                "relationships": {
>                    "xes": {
>                        "links": {
>                            "related": "http://xyz.test/api/v1/ys/2/xes",
>                            "self": "http://xyz.test/api/v1/ys/2/relationships/xes"
>                        }
>                    }
>                },
>                "type": "ys"
>            },
>            {
>                "attributes": {
>                    "createdAt": "2020-11-23T19:16:56.000000Z",
>                    "name": "x2y1",
>                    "updatedAt": "2020-11-23T19:16:56.000000Z"
>                },
>                "id": "3",
>                "links": {
>                    "self": "http://xyz.test/api/v1/ys/3"
>                },
>                "relationships": {
>                    "xes": {
>                        "links": {
>                            "related": "http://xyz.test/api/v1/ys/3/xes",
>                            "self": "http://xyz.test/api/v1/ys/3/relationships/xes"
>                        }
>                    }
>                },
>                "type": "ys"
>            },
>            {
>                "attributes": {
>                    "createdAt": "2020-11-23T19:16:56.000000Z",
>                    "name": "x2y2",
>                    "updatedAt": "2020-11-23T19:16:56.000000Z"
>                },
>                "id": "4",
>                "links": {
>                    "self": "http://xyz.test/api/v1/ys/4"
>                },
>                "relationships": {
>                    "xes": {
>                        "links": {
>                            "related": "http://xyz.test/api/v1/ys/4/xes",
>                            "self": "http://xyz.test/api/v1/ys/4/relationships/xes"
>                        }
>                    }
>                },
>                "type": "ys"
>            }
>        ]
>    }

    curl http://xyz.test/api/v1/zs

>    {
>        "data": [
>            {
>                "attributes": {
>                    "createdAt": "2020-11-23T19:16:56.000000Z",
>                    "name": "x1z1",
>                    "updatedAt": "2020-11-23T19:16:56.000000Z"
>                },
>                "id": "1",
>                "links": {
>                    "self": "http://xyz.test/api/v1/zs/1"
>                },
>                "relationships": {
>                    "xes": {
>                        "links": {
>                            "related": "http://xyz.test/api/v1/zs/1/xes",
>                            "self": "http://xyz.test/api/v1/zs/1/relationships/xes"
>                        }
>                    }
>                },
>                "type": "zs"
>            },
>            {
>                "attributes": {
>                    "createdAt": "2020-11-23T19:16:56.000000Z",
>                    "name": "x1z2",
>                    "updatedAt": "2020-11-23T19:16:56.000000Z"
>                },
>                "id": "2",
>                "links": {
>                    "self": "http://xyz.test/api/v1/zs/2"
>                },
>                "relationships": {
>                    "xes": {
>                        "links": {
>                            "related": "http://xyz.test/api/v1/zs/2/xes",
>                            "self": "http://xyz.test/api/v1/zs/2/relationships/xes"
>                        }
>                    }
>                },
>                "type": "zs"
>            },
>            {
>                "attributes": {
>                    "createdAt": "2020-11-23T19:16:56.000000Z",
>                    "name": "x2z1",
>                    "updatedAt": "2020-11-23T19:16:56.000000Z"
>                },
>                "id": "3",
>                "links": {
>                    "self": "http://xyz.test/api/v1/zs/3"
>                },
>                "relationships": {
>                    "xes": {
>                        "links": {
>                            "related": "http://xyz.test/api/v1/zs/3/xes",
>                            "self": "http://xyz.test/api/v1/zs/3/relationships/xes"
>                        }
>                    }
>                },
>                "type": "zs"
>            },
>            {
>                "attributes": {
>                    "createdAt": "2020-11-23T19:16:56.000000Z",
>                    "name": "x2z2",
>                    "updatedAt": "2020-11-23T19:16:56.000000Z"
>                },
>                "id": "4",
>                "links": {
>                    "self": "http://xyz.test/api/v1/zs/4"
>                },
>                "relationships": {
>                    "xes": {
>                        "links": {
>                            "related": "http://xyz.test/api/v1/zs/4/xes",
>                            "self": "http://xyz.test/api/v1/zs/4/relationships/xes"
>                        }
>                    }
>                },
>                "type": "zs"
>            }
>        ]
>    }