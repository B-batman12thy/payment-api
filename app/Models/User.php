<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    /**
     * Attributs assignables en masse.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'balance', // garde-le uniquement si la colonne existe dans la table users
    ];

    /**
     * Attributs cachés lors de la sérialisation.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casts d'attributs.
     * - 'password' => 'hashed' : Laravel hash automatiquement le mot de passe.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'balance'           => 'decimal:2',
    ];

    /**
     * Relations.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Implémentation JWTSubject.
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
