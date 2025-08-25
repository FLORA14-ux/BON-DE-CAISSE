<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditTrail extends Model
{
    use HasFactory;

    protected $table = 'audit_trail';

    protected $fillable = [
        'table_concernee',
        'enregistrement_id',
        'action',
        'champ_modifie',
        'ancienne_valeur',
        'nouvelle_valeur',
        'description',
        'user_id',
        'adresse_ip',
        'user_agent',
        'date_action'
    ];

    protected $casts = [
        'date_action' => 'datetime',
    ];

    // RELATIONS

    /**
     * Utilisateur qui a effectué l'action
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // SCOPES

    /**
     * Scope par table
     */
    public function scopeForTable($query, $table)
    {
        return $query->where('table_concernee', $table);
    }

    /**
     * Scope par enregistrement
     */
    public function scopeForRecord($query, $table, $recordId)
    {
        return $query->where('table_concernee', $table)
                    ->where('enregistrement_id', $recordId);
    }

    /**
     * Scope par utilisateur
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // MÉTHODES STATIQUES

    /**
     * Créer un audit trail pour une action
     */
    public static function logAction($table, $recordId, $action, $data = [])
    {
        return self::create(array_merge([
            'table_concernee' => $table,
            'enregistrement_id' => $recordId,
            'action' => $action,
            'user_id' => auth()->id(),
            'adresse_ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ], $data));
    }
}