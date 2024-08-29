<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'contact_id', 'location_id', 'contact_name', 'first_name', 'last_name', 'company_name', 'email', 'phone', 'dnd', 'type', 'source', 'assigned_to', 'city', 'state', 'postal_code', 'address1', 'date_of_birth', 'business_id', 'tags', 'followers', 'country', 'additional_emails', 'attributions', 'custom_fields', 'date_added',
    ];
}
