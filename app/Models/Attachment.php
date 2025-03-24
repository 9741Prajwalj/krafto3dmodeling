<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model {
    protected $fillable = [
        'box_face_id', 'type', 'content', 'font_style', 'font_size',
        'position_x', 'position_y', 'position_z',
        'rotation_x', 'rotation_y', 'rotation_z',
        'scale_x', 'scale_y', 'scale_z'
    ];

    public function boxFace() {
        return $this->belongsTo(BoxFace::class);
    }
}