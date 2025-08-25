<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compteur extends Model
{
    use HasFactory;

    protected $table = 'compteurs';

    protected $fillable = [
        'type_document',
        'annee',
        'compteur'
    ];

    protected $casts = [
        'annee' => 'integer',
        'compteur' => 'integer',
    ];

    // MÉTHODES STATIQUES UTILES

    /**
     * Obtenir le prochain numéro pour un type de document
     */
    public static function getNextNumber($typeDocument, $annee = null)
    {
        $annee = $annee ?? date('Y');
        
        $compteur = self::firstOrCreate(
            [
                'type_document' => $typeDocument,
                'annee' => $annee
            ],
            ['compteur' => 0]
        );

        $compteur->increment('compteur');
        
        return self::formatNumber($typeDocument, $compteur->compteur, $annee);
    }

    /**
     * Formater le numéro selon le type de document
     */
    public static function formatNumber($typeDocument, $compteur, $annee)
    {
        $prefix = match($typeDocument) {
            'bon_menues_depenses' => 'BON',
            'lettre_commande' => 'LET',
            'fiche_depenses' => 'FICHE',
            default => 'DOC'
        };

        return sprintf('%s%s-%03d', $prefix, $annee, $compteur);
    }

    /**
     * Obtenir le compteur actuel pour un type de document
     */
    public static function getCurrentCounter($typeDocument, $annee = null)
    {
        $annee = $annee ?? date('Y');
        
        $compteur = self::where('type_document', $typeDocument)
                       ->where('annee', $annee)
                       ->first();

        return $compteur ? $compteur->compteur : 0;
    }

    /**
     * Réinitialiser les compteurs pour une nouvelle année
     */
    public static function resetForNewYear($annee)
    {
        $types = ['bon_menues_depenses', 'lettre_commande', 'fiche_depenses'];
        
        foreach ($types as $type) {
            self::firstOrCreate(
                [
                    'type_document' => $type,
                    'annee' => $annee
                ],
                ['compteur' => 0]
            );
        }
    }
}