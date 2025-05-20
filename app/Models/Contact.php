<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contact extends Model
{
    protected $table = 'contacts';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $timestamps = true;
    public $incrementing = true;

    // relation between contact and user
    public function user(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'user_id', 'id');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class, 'contact_id', 'id');
    }
}
