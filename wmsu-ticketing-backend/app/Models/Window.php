<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Window extends Model
{
  use HasFactory;

  protected $fillable = [
    'window_number',
    'assigned_id',
    'type',
    'method',
    'status',
  ];

  public function teller()
  {
    return $this->belongsTo(related: User::class, foreignKey: 'assigned_id');
  }
}
