<?php

return [
    /*
   |--------------------------------------------------------------------------
   | Schema definition files
   |--------------------------------------------------------------------------
   |
   | This package can generate a schema for you from multiple definition files.
   | Queries, Types and Mutations should always live in a seperate folder.
   | Here you can define the paths that should be imported, relative to your base path
   |
   */
    'schema_paths' => [
        'mutations' => 'app/Http/GraphQL/Mutations',
        'queries' => 'app/Http/GraphQL/Queries',
        'types' => 'app/Http/GraphQL/Types',
    ],
];