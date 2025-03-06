<?php

namespace App\Controllers;

use App\Models\AuthorModel;
use CodeIgniter\RESTful\ResourceController;

class AuthorsController extends ResourceController
{
    protected $modelName = AuthorModel::class;
    protected $format    = 'json';

    private function getAuthenticatedUser()
    {
        $authHeader = $this->request->getHeaderLine('Authorization');
    
        if (!$authHeader) {
            return null;
        }
    
        $token = str_replace('Bearer ', '', $authHeader);
        $userModel = new \App\Models\UserModel();
        return $userModel->where('token', $token)->first();
    }
    // Επιστροφή λίστας συγγραφέων
    // public function index()
    // {
    //     return $this->respond($this->model->findAll());
    // }

    public function index()
    {
        $user = $this->getAuthenticatedUser();
        if (!$user) {
            return $this->failUnauthorized("Μη έγκυρο ή ληγμένο token.");
        }
    
        // Επιστροφή συγγραφέων που ανήκουν στον χρήστη
        $authors = $this->model->where('user_id', $user['id'])->findAll();
        return $this->respond($authors);
    }
    
    
//    public function create()
//     {
//         $data = $this->request->getJSON(true);

//         if (!isset($data['name']) || !isset($data['date_of_birth'])) {
//             return $this->fail("Το όνομα και η ημερομηνία γέννησης είναι υποχρεωτικά.");
//         }

//         // Έλεγχος αν υπάρχει ήδη ο συγγραφέας
//         $existingAuthor = $this->model->where('name', $data['name'])->first();
//         if ($existingAuthor) {
//             return $this->fail("Ο συγγραφέας υπάρχει ήδη.", 409);
//         }

//         $author = [
//             'name' => $data['name'],
//             'date_of_birth' => $data['date_of_birth'],
//         ];

//         if (!$this->model->insert($author)) {
//             return $this->failValidationErrors($this->model->errors());
//         }

//         return $this->respondCreated([
//             'message' => 'Ο συγγραφέας δημιουργήθηκε επιτυχώς!',
//             'author'  => [
//                 'id' => (int) $this->model->insertID(),
//                 'name' => $data['name'],
//                 'date_of_birth' => $data['date_of_birth'],
//             ]
//         ]);
//     }

public function create()
{
    $user = $this->getAuthenticatedUser();
    if (!$user) {
        return $this->failUnauthorized("Μη έγκυρο ή ληγμένο token.");
    }

    $data = $this->request->getJSON(true);

    if (!isset($data['name']) || !isset($data['date_of_birth'])) {
        return $this->fail("Το όνομα και η ημερομηνία γέννησης είναι υποχρεωτικά.");
    }

    // Έλεγχος αν υπάρχει ήδη ο συγγραφέας για τον συγκεκριμένο χρήστη
    $existingAuthor = $this->model
        ->where('name', $data['name'])
        ->where('user_id', $user['id'])
        ->first();

    if ($existingAuthor) {
        return $this->fail("Ο συγγραφέας υπάρχει ήδη για αυτόν τον χρήστη.", 409);
    }

    $author = [
        'name' => $data['name'],
        'date_of_birth' => $data['date_of_birth'],
        'user_id' => $user['id'], // Σύνδεση του συγγραφέα με τον χρήστη
    ];

    if (!$this->model->insert($author)) {
        return $this->failValidationErrors($this->model->errors());
    }

    return $this->respondCreated([
        'message' => 'Ο συγγραφέας δημιουργήθηκε επιτυχώς!',
        'author'  => [
            'id' => (int) $this->model->insertID(),
            'name' => $data['name'],
            'date_of_birth' => $data['date_of_birth'],
        ]
    ]);
}

    // Ενημέρωση συγγραφέα
    public function update($id = null)
    {
        $data = $this->request->getJSON(true);

        if (!isset($data['name'])) {
            return $this->fail("Το όνομα είναι υποχρεωτικό για ενημέρωση.");
        }

        if (!$this->model->find($id)) {
            return $this->failNotFound("Ο συγγραφέας δεν βρέθηκε.");
        }

        $this->model->update($id, $data);
        return $this->respond(['message' => 'Ο συγγραφέας ενημερώθηκε επιτυχώς.']);
    }

    // Διαγραφή συγγραφέα (μόνο αν δεν έχει βιβλία)

    public function delete($id = null)
{
    $user = $this->getAuthenticatedUser();
    if (!$user) {
        return $this->failUnauthorized("Μη έγκυρο ή ληγμένο token.");
    }

    $author = $this->model->find($id);

    if (!$author) {
        return $this->failNotFound("Ο συγγραφέας δεν βρέθηκε.");
    }

    // Έλεγχος αν ο συγγραφέας ανήκει στον χρήστη
    if ($author['user_id'] != $user['id']) {
        return $this->failForbidden("Δεν έχεις άδεια να διαγράψεις αυτόν τον συγγραφέα.");
    }

    $db = \Config\Database::connect();

    // Έλεγχος αν ο συγγραφέας έχει βιβλία
    $query = $db->query("SELECT COUNT(*) AS book_count FROM books WHERE author_id = ?", [$id]);
    $result = $query->getRow();

    if ($result->book_count > 0) {
        return $this->fail("Ο συγγραφέας δεν μπορεί να διαγραφεί γιατί έχει βιβλία.");
    }

    $this->model->delete($id);
    return $this->respondDeleted(['message' => 'Ο συγγραφέας διαγράφηκε επιτυχώς.']);
}

    // public function delete($id = null)
    // {
    //     $db = \Config\Database::connect();

    //     // Έλεγχος αν ο συγγραφέας έχει βιβλία
    //     $query = $db->query("SELECT COUNT(*) AS book_count FROM books WHERE author_id = ?", [$id]);
    //     $result = $query->getRow();

    //     if ($result->book_count > 0) {
    //         return $this->fail("Ο συγγραφέας δεν μπορεί να διαγραφεί γιατί έχει βιβλία.");
    //     }

    //     if (!$this->model->find($id)) {
    //         return $this->failNotFound("Ο συγγραφέας δεν βρέθηκε.");
    //     }

    //     $this->model->delete($id);
    //     return $this->respondDeleted(['message' => 'Ο συγγραφέας διαγράφηκε επιτυχώς.']);
    // }
}