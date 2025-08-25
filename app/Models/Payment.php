<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'description',
        'amount',
        'status',
        'receipt_path',
        'category',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Accessors
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'pending' => 'En attente',
            'completed' => 'Terminé',
            'failed' => 'Échoué',
            default => 'Inconnu',
        };
    }

    public function getCategoryLabelAttribute()
    {
        return match($this->category) {
            'electricity' => 'Électricité',
            'internet' => 'Internet',
            'water' => 'Eau',
            'rent' => 'Loyer',
            'other' => 'Autre',
            default => 'Autre',
        };
    }
}