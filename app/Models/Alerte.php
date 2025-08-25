<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alerte extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'type_alerte',
        'titre',
        'message',
        'niveau',
        'statut',
        'date_resolution',
        'date_creation',
        'date_modification'
    ];

    protected $casts = [
        'date_resolution' => 'datetime',
        'date_creation' => 'datetime',
        'date_modification' => 'datetime',
    ];

    // RELATIONS

    /**
     * Document associé à l'alerte
     */
    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    // SCOPES

    /**
     * Scope pour alertes actives
     */
    public function scopeActive($query)
    {
        return $query->where('statut', 'active');
    }

    /**
     * Scope par type d'alerte
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type_alerte', $type);
    }

    /**
     * Scope par niveau
     */
    public function scopeByNiveau($query, $niveau)
    {
        return $query->where('niveau', $niveau);
    }

    // MÉTHODES UTILES

    /**
     * Marquer l'alerte comme résolue
     */
    public function markAsResolved()
    {
        $this->statut = 'resolue';
        $this->date_resolution = now();
        return $this->save();
    }

    /**
     * Marquer l'alerte comme ignorée
     */
    public function markAsIgnored()
    {
        $this->statut = 'ignoree';
        $this->date_resolution = now();
        return $this->save();
    }

    /**
     * Créer une alerte automatique pour retard de régularisation
     */
    public static function createRetardRegularisation($documentId, $joursRetard)
    {
        return self::create([
            'document_id' => $documentId,
            'type_alerte' => 'regularisation_retard',
            'titre' => 'Retard de régularisation',
            'message' => "Document en retard de régularisation depuis {$joursRetard} jours",
            'niveau' => $joursRetard > 45 ? 'error' : 'warning',
            'statut' => 'active'
        ]);
    }

    /**
     * Créer une alerte pour document non signé
     */
    public static function createDocumentNonSigne($documentId, $description)
    {
        return self::create([
            'document_id' => $documentId,
            'type_alerte' => 'document_non_signe',
            'titre' => 'Document non signé',
            'message' => $description,
            'niveau' => 'warning',
            'statut' => 'active'
        ]);
    }

    /**
     * Obtenir la classe CSS selon le niveau
     */
    public function getCssClassAttribute()
    {
        return match($this->niveau) {
            'error' => 'alert-danger',
            'warning' => 'alert-warning',
            'info' => 'alert-info',
            default => 'alert-secondary'
        };
    }

    /**
     * Obtenir l'icône selon le niveau
     */
    public function getIconAttribute()
    {
        return match($this->niveau) {
            'error' => 'fas fa-exclamation-triangle',
            'warning' => 'fas fa-exclamation-circle',
            'info' => 'fas fa-info-circle',
            default => 'fas fa-bell'
        };
    }
}
