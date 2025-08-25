<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Demandeur extends Model
{
    use HasFactory;

    protected $table = 'demandeurs';

    protected $fillable = [
        'nom',
        'prenom',
        'matricule',
        'service',
        'poste',
        'telephone',
        'email',
        'statut',
        'date_creation',
        'date_modification'
    ];

    protected $casts = [
        'date_creation' => 'datetime',
        'date_modification' => 'datetime',
    ];

    // RELATIONS

    /**
     * Documents demandés par ce demandeur
     */
    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Documents en cours pour ce demandeur
     */
    public function documentsEnCours()
    {
        return $this->hasMany(Document::class)
                   ->whereNotIn('statut', ['regularise', 'annule']);
    }

    /**
     * Documents régularisés par ce demandeur
     */
    public function documentsRegularises()
    {
        return $this->hasMany(Document::class)
                   ->where('statut', 'regularise');
    }

    // SCOPES ET MÉTHODES UTILES

    /**
     * Scope pour demandeurs actifs
     */
    public function scopeActifs($query)
    {
        return $query->where('statut', 'actif');
    }

    /**
     * Scope par service
     */
    public function scopeByService($query, $service)
    {
        return $query->where('service', $service);
    }

    /**
     * Nom complet du demandeur
     */
    public function getFullNameAttribute()
    {
        return $this->prenom . ' ' . $this->nom;
    }

    /**
     * Montant total des demandes en cours
     */
    public function getMontantTotalEnCoursAttribute()
    {
        return $this->documentsEnCours()->sum('montant_fcfa');
    }

    /**
     * Nombre de demandes non régularisées
     */
    public function getNombreDemandesNonRegulariseesAttribute()
    {
        return $this->documentsEnCours()->count();
    }
}