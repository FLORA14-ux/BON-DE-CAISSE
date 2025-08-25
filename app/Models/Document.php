<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero',
        'type_document',
        'demandeur_id',
        'fournisseur',
        'objet_achat',
        'montant_fcfa',
        'facture_proforma_numero',
        'statut',
        'date_creation',
        'date_signature',
        'date_retrait_fonds',
        'date_regularisation',
        'facture_definitive_numero',
        'montant_reel',
        'delai_regularisation_jours',
        'alerte_envoyee',
        'created_by',
        'date_modification'
    ];

    protected $casts = [
        'montant_fcfa' => 'decimal:2',
        'montant_reel' => 'decimal:2',
        'alerte_envoyee' => 'boolean',
        'date_creation' => 'datetime',
        'date_signature' => 'datetime',
        'date_retrait_fonds' => 'datetime',
        'date_regularisation' => 'datetime',
        'date_modification' => 'datetime',
    ];

    // RELATIONS

    /**
     * Demandeur du document
     */
    public function demandeur()
    {
        return $this->belongsTo(Demandeur::class);
    }

    /**
     * Utilisateur qui a créé le document
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Fiche de dépense associée (pour régularisation)
     */
    public function ficheDepense()
    {
        return $this->hasOne(FicheDepense::class);
    }

    /**
     * PDFs archivés pour ce document
     */
    public function pdfsArchives()
    {
        return $this->hasMany(PdfArchive::class);
    }

    /**
     * Alertes liées à ce document
     */
    public function alertes()
    {
        return $this->hasMany(Alerte::class);
    }

    /**
     * Alertes actives pour ce document
     */
    public function alertesActives()
    {
        return $this->hasMany(Alerte::class)->where('statut', 'active');
    }

    // SCOPES

    /**
     * Scope par statut
     */
    public function scopeByStatut($query, $statut)
    {
        return $query->where('statut', $statut);
    }

    /**
     * Scope par type de document
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type_document', $type);
    }

    /**
     * Scope pour documents en retard de régularisation
     */
    public function scopeEnRetardRegularisation($query, $jours = 30)
    {
        return $query->where('statut', 'fonds_retires')
                    ->whereNotNull('date_retrait_fonds')
                    ->whereRaw('DATEDIFF(NOW(), date_retrait_fonds) > ?', [$jours]);
    }

    /**
     * Scope pour documents de l'année en cours
     */
    public function scopeCurrentYear($query)
    {
        return $query->whereYear('date_creation', date('Y'));
    }

    // MÉTHODES UTILES

    /**
     * Déterminer automatiquement le type selon le montant
     */
    public static function determineTypeDocument($montant)
    {
        return $montant <= 500000 ? 'bon_menues_depenses' : 'lettre_commande';
    }

    /**
     * Générer un numéro automatique
     */
    public function generateNumero()
    {
        $this->numero = Compteur::getNextNumber($this->type_document);
        return $this->numero;
    }

    /**
     * Calculer les jours depuis le retrait des fonds
     */
    public function getJoursDepuisRetraitAttribute()
    {
        if (!$this->date_retrait_fonds) {
            return null;
        }
        return Carbon::parse($this->date_retrait_fonds)->diffInDays(now());
    }

    /**
     * Vérifier si le document est en retard de régularisation
     */
    public function isEnRetardRegularisation($delaiMax = 30)
    {
        if ($this->statut !== 'fonds_retires' || !$this->date_retrait_fonds) {
            return false;
        }
        return $this->jours_depuis_retrait > $delaiMax;
    }

    /**
     * Obtenir la différence entre montant proforma et réel
     */
    public function getDifferenceAttribute()
    {
        if (!$this->montant_reel) {
            return null;
        }
        return $this->montant_fcfa - $this->montant_reel;
    }

    /**
     * Vérifier si le document peut être régularisé
     */
    public function canBeRegularized()
    {
        return $this->statut === 'fonds_retires';
    }

    /**
     * Mettre à jour le statut avec audit
     */
    public function updateStatut($nouveauStatut, $userId)
    {
        $ancienStatut = $this->statut;
        $this->statut = $nouveauStatut;
        
        // Mettre à jour les dates selon le statut
        switch ($nouveauStatut) {
            case 'signe':
                $this->date_signature = now();
                break;
            case 'fonds_retires':
                $this->date_retrait_fonds = now();
                break;
            case 'regularise':
                $this->date_regularisation = now();
                break;
        }
        
        $this->save();

        // Créer l'audit trail
        AuditTrail::create([
            'table_concernee' => 'documents',
            'enregistrement_id' => $this->id,
            'action' => 'modification',
            'champ_modifie' => 'statut',
            'ancienne_valeur' => $ancienStatut,
            'nouvelle_valeur' => $nouveauStatut,
            'user_id' => $userId,
            'adresse_ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}