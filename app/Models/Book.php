<?php

namespace App\Models;

use CodeIgniter\Model;

class BookModel extends Model
{
    protected $table      = 'books';
    protected $primaryKey = 'id';
    protected $allowedFields = ['title', 'author_id', 'published_year','user_id'];
}