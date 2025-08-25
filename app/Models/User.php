<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'username',
        'password',
        'bureau',
        'nom',
        'prenom',
        'email',
        'statut',
        'tentatives_connexion',
        'compte_bloque',
        'date_creation',
        'date_modification'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'compte_bloque' => 'boolean',
        'date_creation' => 'datetime',
        'date_modification' => 'datetime',
    ];

    // RELATIONS

    /**
     * Documents créés par cet utilisateur
     */
    public function documentsCreated()
    {
        return $this->hasMany(Document::class, 'created_by');
    }

    /**
     * Fiches de dépenses créées par cet utilisateur
     */
    public function fichesDepensesCreated()
    {
        return $this->hasMany(FicheDepense::class, 'created_by');
    }

    /**
     * Actions d'audit effectuées par cet utilisateur
     */
    public function auditTrails()
    {
        return $this->hasMany(AuditTrail::class);
    }

    /**
     * PDFs générés par cet utilisateur
     */
    public function pdfsGenerated()
    {
        return $this->hasMany(PdfArchive::class, 'generated_by');
    }

    // SCOPES ET MÉTHODES UTILES

    /**
     * Scope pour utilisateurs actifs
     */
    public function scopeActifs($query)
    {
        return $query->where('statut', 'actif');
    }

    /**
     * Scope par bureau
     */
    public function scopeByBureau($query, $bureau)
    {
        return $query->where('bureau', $bureau);
    }

    /**
     * Vérifier si le compte est bloqué
     */
    public function isBlocked()
    {
        return $this->compte_bloque;
    }

    /**
     * Nom complet de l'utilisateur
     */
    public function getFullNameAttribute()
    {
        return $this->prenom . ' ' . $this->nom;
    }
}