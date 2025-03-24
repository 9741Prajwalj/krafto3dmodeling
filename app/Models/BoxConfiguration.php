<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoxConfiguration extends Model {
    protected $fillable = [
        'breadth', 'length', 'height', 'thickness', 'flute_freq', 'model_path', 'image_path'
    ];

    public function faces() {
        return $this->hasMany(BoxFace::class);
    }
}