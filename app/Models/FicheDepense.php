<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FicheDepense extends Model
{
    use HasFactory;

    protected $table = 'fiches_depenses';

    protected $fillable = [
        'document_id',
        'numero_fiche',
        'montant_reel',
        'difference',
        'observations',
        'signature_etabli',
        'signature_visa_controle',
        'signature_visa_chef_financier',
        'signature_directeur_financier',
        'signature_beneficiaire',
        'signature_caissier',
        'date_signature_etabli',
        'date_signature_visa_controle',
        'date_signature_visa_chef_financier',
        'date_signature_directeur_financier',
        'date_signature_beneficiaire',
        'date_signature_caissier',
        'statut',
        'created_by',
        'date_creation',
        'date_modification'
    ];

    protected $casts = [
        'montant_reel' => 'decimal:2',
        'difference' => 'decimal:2',
        'signature_etabli' => 'boolean',
        'signature_visa_controle' => 'boolean',
        'signature_visa_chef_financier' => 'boolean',
        'signature_directeur_financier' => 'boolean',
        'signature_beneficiaire' => 'boolean',
        'signature_caissier' => 'boolean',
        'date_signature_etabli' => 'datetime',
        'date_signature_visa_controle' => 'datetime',
        'date_signature_visa_chef_financier' => 'datetime',
        'date_signature_directeur_financier' => 'datetime',
        'date_signature_beneficiaire' => 'datetime',
        'date_signature_caissier' => 'datetime',
        'date_creation' => 'datetime',
        'date_modification' => 'datetime',
    ];

    // RELATIONS

    /**
     * Document associé à cette fiche
     */
    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Utilisateur qui a créé la fiche
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * PDFs archivés pour cette fiche
     */
    public function pdfsArchives()
    {
        return $this->hasMany(PdfArchive::class, 'fiche_depense_id');
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
     * Scope pour fiches complètes (toutes signatures)
     */
    public function scopeComplete($query)
    {
        return $query->where('statut', 'complete');
    }

    /**
     * Scope pour fiches en cours de signature
     */
    public function scopeEnSignature($query)
    {
        return $query->where('statut', 'en_signature');
    }

    // MÉTHODES UTILES

    /**
     * Générer le numéro de fiche automatiquement
     */
    public function generateNumero()
    {
        $this->numero_fiche = Compteur::getNextNumber('fiche_depenses');
        return $this->numero_fiche;
    }

    /**
     * Calculer automatiquement la différence
     */
    public function calculateDifference()
    {
        if ($this->document && $this->montant_reel) {
            $this->difference = $this->document->montant_fcfa - $this->montant_reel;
        }
        return $this->difference;
    }

    /**
     * Vérifier si toutes les signatures sont complètes
     */
    public function areAllSignaturesComplete()
    {
        return $this->signature_etabli &&
               $this->signature_visa_controle &&
               $this->signature_visa_chef_financier &&
               $this->signature_directeur_financier &&
               $this->signature_beneficiaire &&
               $this->signature_caissier;
    }

    /**
     * Obtenir la liste des signatures manquantes
     */
    public function getMissingSignatures()
    {
        $signatures = [
            'signature_etabli' => 'Établi',
            'signature_visa_controle' => 'Visa de contrôle',
            'signature_visa_chef_financier' => 'Visa du chef financier',
            'signature_directeur_financier' => 'Directeur Financier',
            'signature_beneficiaire' => 'Bénéficiaire',
            'signature_caissier' => 'Caissier'
        ];

        $manquantes = [];
        foreach ($signatures as $field => $label) {
            if (!$this->$field) {
                $manquantes[] = $label;
            }
        }

        return $manquantes;
    }

    /**
     * Marquer une signature comme complète
     */
    public function markSignatureComplete($typeSignature, $userId)
    {
        $dateField = 'date_' . $typeSignature;
        
        $this->$typeSignature = true;
        $this->$dateField = now();
        
        // Vérifier si toutes les signatures sont complètes
        if ($this->areAllSignaturesComplete()) {
            $this->statut = 'complete';
            
            // Mettre à jour le statut du document associé
            $this->document->updateStatut('regularise', $userId);
        } else {
            $this->statut = 'en_signature';
        }
        
        $this->save();

        // Audit trail
        AuditTrail::create([
            'table_concernee' => 'fiches_depenses',
            'enregistrement_id' => $this->id,
            'action' => 'modification',
            'champ_modifie' => $typeSignature,
            'ancienne_valeur' => 'false',
            'nouvelle_valeur' => 'true',
            'description' => "Signature {$typeSignature} marquée comme complète",
            'user_id' => $userId,
            'adresse_ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Obtenir le pourcentage de completion des signatures
     */
    public function getSignatureCompletionPercentage()
    {
        $total = 6; // Nombre total de signatures requises
        $completed = 0;
        
        $signatures = [
            'signature_etabli',
            'signature_visa_controle', 
            'signature_visa_chef_financier',
            'signature_directeur_financier',
            'signature_beneficiaire',
            'signature_caissier'
        ];
        
        foreach ($signatures as $signature) {
            if ($this->$signature) {
                $completed++;
            }
        }
        
        return round(($completed / $total) * 100, 2);
    }
}