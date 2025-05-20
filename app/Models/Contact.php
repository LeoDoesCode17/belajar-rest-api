<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contact extends Model
{
    protected $table = 'contacts';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $timestamps = true;
    public $incrementing = true;
}
