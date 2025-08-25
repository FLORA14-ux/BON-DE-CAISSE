<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PdfArchive extends Model
{
    use HasFactory;

    protected $table = 'pdfs_archives';

    protected $fillable = [
        'document_id',
        'fiche_depense_id',
        'type_pdf',
        'nom_fichier',
        'chemin_fichier',
        'generated_by',
        'date_generation'
    ];

    protected $casts = [
        'date_generation' => 'datetime',
    ];

    // RELATIONS

    /**
     * Document associé
     */
    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Fiche de dépense associée
     */
    public function ficheDepense()
    {
        return $this->belongsTo(FicheDepense::class);
    }

    /**
     * Utilisateur qui a généré le PDF
     */
    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    // SCOPES

    /**
     * Scope par type de PDF
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type_pdf', $type);
    }

    // MÉTHODES UTILES

    /**
     * Vérifier si le fichier existe
     */
    public function fileExists()
    {
        return Storage::exists($this->chemin_fichier);
    }

    /**
     * Obtenir la taille du fichier
     */
    public function getFileSize()
    {
        return $this->fileExists() ? Storage::size($this->chemin_fichier) : 0;
    }

    /**
     * Obtenir l'URL de téléchargement
     */
    public function getDownloadUrl()
    {
        return route('pdf.download', $this->id);
    }

    /**
     * Supprimer le fichier et l'enregistrement
     */
    public function deleteFile()
    {
        if ($this->fileExists()) {
            Storage::delete($this->chemin_fichier);
        }
        return $this->delete();
    }
}
