<?php


use CodeIgniter\Router\RouteCollection;
// use CodeIgniter\HTTP\Response;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// Routes για Users
$routes->post('users', 'UsersController::create');  // Δημιουργία χρήστη
$routes->post('login', 'UsersController::login'); 
$routes->get('users', 'UsersController::index');  // Λίστα χρηστών
$routes->delete('users/(:num)', 'UsersController::delete/$1');  // Διαγραφή χρήστη

// Routes για βιβλία 
$routes->get('books', 'Books::index');  // Λίστα βιβλίων (φιλτραρισμένα με βάση τον χρήστη)
$routes->post('books', 'Books::create');  // Προσθήκη βιβλίου
$routes->get('books/(:num)', 'Books::show/$1');  // Προβολή συγκεκριμένου βιβλίου
$routes->put('books/(:num)', 'Books::update/$1');  // Ενημέρωση βιβλίου
$routes->delete('books/(:num)', 'Books::delete/$1');  // Διαγραφή βιβλίου
//routes για συγγραφείς
$routes->group('authors', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'AuthorsController::index');    // GET /authors  -> Λίστα συγγραφέων
    $routes->post('/', 'AuthorsController::create');  // POST /authors -> Δημιουργία συγγραφέα
    $routes->put('(:num)', 'AuthorsController::update/$1'); // PUT /authors/{id} -> Ενημέρωση
    $routes->delete('(:num)', 'AuthorsController::delete/$1'); // DELETE /authors/{id} -> Διαγραφή
});


// CORS preflight requests
$routes->options('(:any)', function () {
    $response = service('response');
    return $response
        ->setHeader('Access-Control-Allow-Origin', '*')
        ->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, DELETE, PATCH')
        ->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With')
        ->setStatusCode(200);
});


// service('auth')->routes($routes);