<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $authHeader = $request->getHeaderLine('Authorization');

        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['message' => 'Xωρίς πρόσβαση. Πρέπει να δωθεί token']);
        }

        $token = $matches[1];

        // Συνδέουμε με τη βάση και ελέγχουμε αν το token υπάρχει
        $db = \Config\Database::connect();
        $query = $db->query("SELECT id FROM users WHERE token = ?", [$token]);
        $user = $query->getRow();

        if (!$user) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['message' => 'Χωρίς Πρόσβαση. Μη έγκυρο token']);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Δεν χρειάζεται να κάνουμε κάτι εδώ
    }
}