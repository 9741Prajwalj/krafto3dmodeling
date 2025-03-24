<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoxFace extends Model {
    protected $fillable = ['box_configuration_id', 'face_name', 'has_attachment'];

    public function boxConfiguration() {
        return $this->belongsTo(BoxConfiguration::class);
    }

    public function attachment() {
        return $this->hasOne(Attachment::class);
    }
}