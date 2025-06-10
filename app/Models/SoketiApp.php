<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SoketiApp extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'id';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                do {
                    $id = str_pad(random_int(100000000000, 999999999999), 12, '0', STR_PAD_LEFT);
                } while (static::where('id', $id)->exists());

                $model->id = $id;
            }
        });
    }

    protected $fillable = [
        'id',
        'app_name',
        'app_description',
        'key',
        'secret',
        'max_connections',
        'enable_client_messages',
        'enabled',
        'max_backend_events_per_sec',
        'max_client_events_per_sec',
        'max_read_req_per_sec',
        'webhooks',
        'max_presence_members_per_channel',
        'max_presence_member_size_in_kb',
        'max_channel_name_length',
        'max_event_channels_at_once',
        'max_event_name_length',
        'max_event_payload_in_kb',
        'max_event_batch_size',
        'enable_user_authentication'
    ];

    protected $casts = [
        'max_connections' => 'integer',
        'enable_client_messages' => 'boolean',
        'enabled' => 'boolean',
        'max_backend_events_per_sec' => 'integer',
        'max_client_events_per_sec' => 'integer',
        'max_read_req_per_sec' => 'integer',
        'webhooks' => 'json',
        'max_presence_members_per_channel' => 'integer',
        'max_presence_member_size_in_kb' => 'integer',
        'max_channel_name_length' => 'integer',
        'max_event_channels_at_once' => 'integer',
        'max_event_name_length' => 'integer',
        'max_event_payload_in_kb' => 'integer',
        'max_event_batch_size' => 'integer',
        'enable_user_authentication' => 'boolean'
    ];

    protected $attributes = [
        'max_connections' => 1000,
        'enable_client_messages' => true,
        'enabled' => true
    ];

    public function connections()
    {
        return $this->hasMany(SoketiConnection::class, 'app_id', 'id');
    }

    public function getCurrentConnectionCount()
    {
        return $this->connections()->where('is_connected', true)->count();
    }
}
