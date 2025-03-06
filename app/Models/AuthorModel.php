<?php

namespace App\Models;

use CodeIgniter\Model;

class AuthorModel extends Model
{
    protected $allowedFields = ['name', 'date_of_birth', 'user_id'];
    protected $table      = 'authors';
    protected $primaryKey = 'id';
    protected $useTimestamps = false;
    protected $validationRules = [
        'name'          => 'required|min_length[2]|max_length[255]|is_unique[authors.name]',
        'date_of_birth' => 'required|valid_date[Y-m-d]',
    ];
    
    protected $validationMessages = [
        'name' => [
            'required'    => 'Το όνομα είναι υποχρεωτικό.',
            'min_length'  => 'Το όνομα πρέπει να έχει τουλάχιστον 2 χαρακτήρες.',
            'max_length'  => 'Το όνομα πρέπει να έχει έως 255 χαρακτήρες.',
            'is_unique'   => 'Υπάρχει ήδη συγγραφέας με αυτό το όνομα.',
        ],
        'date_of_birth' => [
            'required'    => 'Η ημερομηνία γέννησης είναι υποχρεωτική.',
            'valid_date'  => 'Η ημερομηνία πρέπει να είναι σε μορφή YYYY-MM-DD.',
        ],
    ];
    
}