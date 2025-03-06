<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users'; // Ονομασία πίνακα
    protected $primaryKey = 'id'; // Πρωτεύον κλειδί

    protected $allowedFields = [
        'username',
        'password',
        'token',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $useTimestamps = true; // Αυτόματη διαχείριση created_at, updated_at
    protected $useSoftDeletes = true; // Soft deletes

    // Κρυπτογράφηση κωδικού πριν την εισαγωγή στη βάση
    protected function setPassword(string $password)
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }
}