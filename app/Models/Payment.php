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
        'category',
        'receipt_path',
        'paid_at',
    ];

    protected $casts = [
        'amount'  => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    protected $appends = ['receipt_url', 'status_label', 'category_label'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getReceiptUrlAttribute()
    {
        return $this->receipt_path ? url('storage/'.$this->receipt_path) : null;
    }

    public function getStatusLabelAttribute()
    {
        return match ($this->status) {
            'PENDING' => 'En attente',
            'SUCCESS' => 'Terminé',
            'FAILED'  => 'Échoué',
            default   => 'Inconnu',
        };
    }

    public function getCategoryLabelAttribute()
    {
        return match ($this->category) {
            'electricity' => 'Électricité',
            'internet'    => 'Internet',
            'water'       => 'Eau',
            'rent'        => 'Loyer',
            'other'       => 'Autre',
            default       => 'Autre',
        };
    }
}
