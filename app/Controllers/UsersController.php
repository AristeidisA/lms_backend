<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\RESTful\ResourceController;

class UsersController extends ResourceController
{
    protected $modelName = UserModel::class;
    protected $format    = 'json';

    // Δημιουργία χρήστη (Register)
    public function create()
    {
        $data = $this->request->getJSON(true);

        if (!isset($data['username']) || !isset($data['password'])) {
            return $this->fail("Username και password είναι υποχρεωτικά.");
        }

        // Έλεγχος αν υπάρχει ήδη το username
        $existingUser = $this->model->where('username', $data['username'])->first();
        if ($existingUser) {
            return $this->fail("Το username υπάρχει ήδη. Διάλεξε κάποιο άλλο.", 409);
        }

        $user = [
            'username' => $data['username'],
            'password' => password_hash($data['password'], PASSWORD_BCRYPT),
        ];

        $this->model->insert($user);
        return $this->respondCreated(['message' => 'Νέος χρήστης δημιουργήθηκε!']);
    }

    // Λίστα χρηστών
    public function index()
    {
        return $this->respond($this->model->findAll());
    }

    // Διαγραφή χρήστη
    public function delete($id = null)
    {
        if (!$this->model->find($id)) {
            return $this->failNotFound("Ο χρήστης δεν βρέθηκε.");
        }

        $this->model->delete($id);
        return $this->respondDeleted(['message' => 'Ο χρήστης διαγράφηκε επιτυχώς.']);
    }

    // Ενημέρωση χρήστη
    public function update($id = null)
    {
        $data = $this->request->getJSON(true);

        if (!isset($data['username'])) {
            return $this->fail("Το username είναι υποχρεωτικό για ενημέρωση.");
        }

        // Έλεγχος αν υπάρχει άλλος χρήστης με το ίδιο username (εκτός του τρέχοντος)
        $existingUser = $this->model
            ->where('username', $data['username'])
            ->where('id !=', $id)
            ->first();

        if ($existingUser) {
            return $this->fail("Το username υπάρχει ήδη. Διάλεξε κάποιο άλλο.", 409);
        }

        if (!$this->model->update($id, $data)) {
            return $this->failValidationErrors($this->model->errors());
        }

        return $this->respond(['message' => 'Ο χρήστης ενημερώθηκε επιτυχώς.']);
    }

    // Login χρήστη
    public function login()
    {
        $data = $this->request->getJSON(true);
    
        if (!isset($data['username']) || !isset($data['password'])) {
            return $this->fail("Username και password είναι υποχρεωτικά.");
        }
    
        // Βρίσκουμε τον χρήστη
        $user = $this->model->where('username', $data['username'])->first();
    
        if (!$user) {
            return $this->failNotFound("Ο χρήστης δεν βρέθηκε.");
        }
    
        if (!password_verify($data['password'], $user['password'])) {
            return $this->fail("Λάθος username ή password. Input: " . $data['password'] . " | DB: " . $user['password']);
        }
        
    
        // Δημιουργία νέου token
        $token = bin2hex(random_bytes(32));
    
        // Έλεγχος αν ο χρήστης υπάρχει πριν ενημερωθεί το token
        if (!$this->model->update($user['id'], ['token' => $token])) {
            return $this->fail("Αποτυχία ενημέρωσης του token.");
        }
    
        // Επιστροφή του token στον χρήστη
        return $this->respond([
            'message' => 'Επιτυχής σύνδεση!',
            'token'   => $token,
            'user'    => [
                'id'       => $user['id'],
                'username' => $user['username']
            ]
        ]);


    }

    
    
    
}