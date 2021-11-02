## virtualizor API Wrapper

this is a laravel package that allows you to access Virtualizor API service for control you server

how to install
-------
- install using composer 
`composer require blackpanda/virtualizor`
- Add Service Provider to `config/app.php`
````
...
/*
* Package Service Providers...
*/

\BlackPanda\Virtualizor\Virtualizor::class,
...
````

- Add Facade To `config/app.php`

````
...
'aliases' => [
...
'Virtualizor' => \BlackPanda\Virtualizor\VirtualizorFacade::class,
];
````

#####How to use

````

 $virtualizor = new \BlackPanda\Virtualizor\Virtualizor('server_ip',4085,'api_key','api_sec');
 $virtualizor->OSTemplates()
````




