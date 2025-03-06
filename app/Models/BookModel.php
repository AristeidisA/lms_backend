<?php

namespace App\Models;

use CodeIgniter\Model;

class BookModel extends Model
{
    protected $table = 'books'; // Το όνομα του πίνακα στη βάση δεδομένων
    protected $primaryKey = 'id';
    protected $allowedFields = ['title', 'author_id', 'publication_year', 'user_id'];
    protected $validationRules = [
        'title'            => 'required|min_length[2]|max_length[255]',
        'publication_year' => 'required|numeric|greater_than[0]',
        'author_id'        => 'required|numeric',
        'user_id'          => 'required|numeric',
    ];
    
    protected $validationMessages = [
        'title' => [
            'required'    => 'Ο τίτλος είναι υποχρεωτικός.',
            'is_unique'   => 'Ο τίτλος υπάρχει ήδη για τον συγγραφέα.',
            'min_length'  => 'Ο τίτλος πρέπει να έχει τουλάχιστον 2 χαρακτήρες.',
            'max_length'  => 'Ο τίτλος πρέπει να έχει έως 255 χαρακτήρες.',
        ],
       
        'publication_year' => [
            'required'    => 'Το έτος έκδοσης είναι υποχρεωτικό.',
            'numeric'     => 'Το έτος πρέπει να είναι αριθμός.',
            'greater_than'=> 'Το έτος έκδοσης πρέπει να είναι μεγαλύτερο από 0.',
        ],
        'author_id' => [
            'required'    => 'Ο συγγραφέας είναι υποχρεωτικός.',
            'numeric'     => 'Το author_id πρέπει να είναι αριθμός.',
        ],
    ];
    
}