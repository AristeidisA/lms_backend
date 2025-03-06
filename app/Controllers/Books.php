<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\BookModel;
use App\Models\UserModel; // Προσθήκη του UserModel για αναζήτηση χρήστη μέσω token

class Books extends ResourceController
{
    protected $bookModel;
    protected $userModel;

    public function __construct()
    {
        $this->bookModel = new BookModel(); 
        $this->userModel = new UserModel(); // Αρχικοποίηση User Model
    }

    /**
     * Λήψη του χρήστη μέσω του token από το Authorization Header
     */
    private function getAuthenticatedUser()
    {
        
        $authHeader = $this->request->getHeaderLine('Authorization');
        log_message('debug', 'Authorization Header Received: ' . $authHeader); // <-- Δες τι φτάνει

        if (!$authHeader) {
            return null;
        }

        $token = str_replace('Bearer ', '', $authHeader);
        log_message('debug', 'Extracted Token: ' . $token); // <-- Δες τι token βγαίνει
        return $this->userModel->where('token', $token)->first();
        

    }

    /**
     * Επιστροφή όλων των βιβλίων του χρήστη
     */
    public function index()
    {
       
        $authHeader = $this->request->getHeaderLine('Authorization');
        
    
        if (!$authHeader) {
            log_message('error', '⛔ Λείπει το authentication token');
            return $this->failUnauthorized("Λείπει το authentication token.");
        }
    
        $token = str_replace('Bearer ', '', $authHeader);
        $user = $this->userModel->where('token', $token)->first();
        
        if (!$user) {
            
            return $this->failUnauthorized("Μη έγκυρο token.");
        }
    
        
        // $books = $this->bookModel->where('user_id', $user['id'])->findAll();
        $books = $this->bookModel
        ->select('books.id, books.title, books.publication_year, books.author_id, authors.name as author_name')
        ->join('authors', 'authors.id = books.author_id')
        ->where('books.user_id', $user['id'])
        ->findAll();

    
        return $this->respond($books);
    }
    

    /**
     * Επιστροφή ενός συγκεκριμένου βιβλίου
     */
    public function show($id = null)
    {
        $user = $this->getAuthenticatedUser();
        if (!$user) {
            return $this->failUnauthorized("Μη έγκυρο ή ληγμένο token.");
        }

        $book = $this->bookModel->where('id', $id)->where('user_id', $user['id'])->first();
        if (!$book) {
            return $this->failNotFound("Το βιβλίο δεν βρέθηκε.");
        }

        return $this->respond($book);
    }

 
public function create()
{
    $user = $this->getAuthenticatedUser();
    if (!$user) {
        log_message('error', '⛔ User authentication failed. No valid token.');
        return $this->failUnauthorized("Μη έγκυρο ή ληγμένο token.");
    }

   

    $data = $this->request->getJSON(true);
    $data['user_id'] = $user['id']; // Σύνδεση του βιβλίου με τον χρήστη
   
    $existingBook = $this->bookModel
        ->where('title', $data['title'])
        ->where('author_id', $data['author_id'])
        ->first();

    if ($existingBook) {
        return $this->fail("Υπάρχει ήδη βιβλίο με αυτόν τον τίτλο για τον συγγραφέα.", 409);
    }

    if (!$this->bookModel->insert($data)) {
        log_message('error', '❌ Insert failed: ' . json_encode($this->bookModel->errors()));
        return $this->failValidationErrors($this->bookModel->errors());
    }

    

    // 🔹 Πάρε το ID του τελευταίου βιβλίου που προστέθηκε
    $bookId = $this->bookModel->insertID();

    // 🔹 Βρες το βιβλίο από τη βάση με το σωστό ID
    $book = $this->bookModel->find($bookId);

    // ✅ Επιστροφή του βιβλίου με το σωστό `id`
    return $this->respondCreated([
        'message' => 'Το βιβλίο δημιουργήθηκε επιτυχώς!',
        'book' => [
            'id' => (int) $book['id'], // Επιστροφή ως αριθμός
            'title' => $book['title'],
            'author_id' => $book['author_id'],
            'publication_year' => $book['publication_year'],
            'user_id' => $book['user_id'],
        ],
    ]);

    
}


    
    /**
     * Ενημέρωση υπάρχοντος βιβλίου
     */
    public function update($id = null)
    {
        $user = $this->getAuthenticatedUser();
        if (!$user) {
            return $this->failUnauthorized("Μη έγκυρο ή ληγμένο token.");
        }

        $data = $this->request->getJSON(true);

        $book = $this->bookModel->where('id', $id)->where('user_id', $user['id'])->first();
        if (!$book) {
            return $this->failNotFound("Το βιβλίο δεν βρέθηκε ή δεν έχετε δικαίωμα επεξεργασίας.");
        }

        // Έλεγχος αν υπάρχει άλλο βιβλίο με τον ίδιο τίτλο και συγγραφέα (εκτός του τρέχοντος)
        $existingBook = $this->bookModel
            ->where('title', $data['title'])
            ->where('author_id', $data['author_id'])
            ->where('id !=', $id)
            ->where('user_id', $user['id'])
            ->first();

        if ($existingBook) {
            return $this->fail("Το βιβλίο υπάρχει ήδη για τον συγκεκριμένο συγγραφέα.", 409);
        }

        if (!$this->bookModel->update($id, $data)) {
            return $this->failValidationErrors($this->bookModel->errors());
        }

        return $this->respondUpdated(['message' => "Το βιβλίο ενημερώθηκε επιτυχώς."]);
    }

    /**
     * Διαγραφή βιβλίου
     */

    public function delete($id = null)
{
    $user = $this->getAuthenticatedUser(); // Παίρνουμε τον συνδεδεμένο χρήστη
    if (!$user) {
        return $this->failUnauthorized("Μη έγκυρο ή ληγμένο token.");
    }

    $book = $this->bookModel->find($id);
    
    if (!$book) {
        return $this->failNotFound("Το βιβλίο δεν βρέθηκε.");
    }

    // **Έλεγχος αν το βιβλίο ανήκει στον χρήστη**
    if ($book['user_id'] != $user['id']) {
        return $this->failForbidden("Δεν έχεις άδεια να διαγράψεις αυτό το βιβλίο.");
    }

    $this->bookModel->delete($id);
    return $this->respondDeleted(['message' => "Το βιβλίο διαγράφηκε επιτυχώς!"]);
}

}