<?php

  namespace App\Models;

  use Illuminate\Database\Eloquent\Factories\HasFactory;
  use Illuminate\Database\Eloquent\Model;
  use Illuminate\Support\Facades\Log;

  class Contact extends Model
  {
      use HasFactory;

      protected $fillable = ['name', 'email', 'message']; // Ensure these fields are fillable
  }
