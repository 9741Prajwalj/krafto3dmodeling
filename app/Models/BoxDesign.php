<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class BoxDesign extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'breadth',
        'length',
        'height',
        'thickness',
        'flute_freq',
        'model_path',
        'image_path',
        'faces',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'breadth' => 'float',
        'length' => 'float',
        'height' => 'float',
        'thickness' => 'float',
        'flute_freq' => 'integer',
        'faces' => 'array', // Automatically cast the JSON to/from array
    ];

    /**
     * Get the URL for the 3D model file.
     *
     * @return string|null
     */
    public function getModelUrlAttribute()
    {
        return $this->model_path ? Storage::url($this->model_path) : null;
    }

    /**
     * Get the URL for the preview image.
     *
     * @return string|null
     */
    public function getImageUrlAttribute()
    {
        return $this->image_path ? Storage::url($this->image_path) : null;
    }

    /**
     * Get a specific face configuration.
     *
     * @param string $faceName
     * @return array|null
     */
    public function getFace(string $faceName)
    {
        return $this->faces[$faceName] ?? null;
    }

    /**
     * Check if a face has an attachment.
     *
     * @param string $faceName
     * @return bool
     */
    public function faceHasAttachment(string $faceName): bool
    {
        return isset($this->faces[$faceName]['has_attachment']) 
            && $this->faces[$faceName]['has_attachment'];
    }

    /**
     * Get the attachment for a specific face.
     *
     * @param string $faceName
     * @return array|null
     */
    public function getFaceAttachment(string $faceName)
    {
        if ($this->faceHasAttachment($faceName)) {
            return $this->faces[$faceName]['attachment'] ?? null;
        }
        return null;
    }
}