# Soketi WebSocket Setup Guide

## Environment Configuration

### Laravel .env
```env
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_HOST=your-soketi-domain.com
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

# For Vite
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
```

### Broadcasting Config (`config/broadcasting.php`)
```php
'pusher' => [
    'driver' => 'pusher',
    'key' => env('PUSHER_APP_KEY'),
    'secret' => env('PUSHER_APP_SECRET'),
    'app_id' => env('PUSHER_APP_ID'),
    'options' => [
        'cluster' => env('PUSHER_APP_CLUSTER'),
        'host' => env('PUSHER_HOST'),
        'port' => env('PUSHER_PORT'),
        'scheme' => env('PUSHER_SCHEME'),
        'encrypted' => true,
        'useTLS' => env('PUSHER_SCHEME') === 'https',
    ],
],
```

## Laravel Backend

### 1. Install Package & Enable Broadcasting
```bash
composer require pusher/pusher-php-server
```

Uncomment in `config/app.php`:
```php
App\Providers\BroadcastServiceProvider::class,
```

### 2. Create Event
```php
<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable;

    public $message;
    public $user;

    public function __construct($message, $user)
    {
        $this->message = $message;
        $this->user = $user;
    }

    public function broadcastOn()
    {
        return new Channel('chat-room');
    }
}
```

### 3. Broadcasting Routes (`routes/channels.php`)
```php
// Public channel
Broadcast::channel('chat-room', function () {
    return true;
});

// Private channel
Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
```

### 4. Trigger Event
```php
broadcast(new MessageSent($message, $user));
```

## Frontend JavaScript

### 1. Install & Initialize
```bash
npm install pusher-js
```

```javascript
import Pusher from 'pusher-js';

const pusher = new Pusher(import.meta.env.VITE_PUSHER_APP_KEY, {
    wsHost: import.meta.env.VITE_PUSHER_HOST,
    wsPort: import.meta.env.VITE_PUSHER_PORT,
    wssHost: import.meta.env.VITE_PUSHER_HOST,
    wssPort: import.meta.env.VITE_PUSHER_PORT,
    forceTLS: import.meta.env.VITE_PUSHER_SCHEME === 'https',
    encrypted: true,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            Authorization: `Bearer ${authToken}`,
        },
    },
});
```

### 2. Subscribe to Channels
```javascript
// Public channel
const channel = pusher.subscribe('chat-room');
channel.bind('MessageSent', (data) => {
    console.log('New message:', data);
});

// Private channel
const privateChannel = pusher.subscribe('private-user.123');
privateChannel.bind('NotificationSent', (data) => {
    console.log('Notification:', data);
});

// Presence channel
const presenceChannel = pusher.subscribe('presence-chat-room');
presenceChannel.bind('pusher:subscription_succeeded', (members) => {
    console.log('Online users:', members.count);
});
```

## Channel Types

- **Public**: `chat-room` - No auth required
- **Private**: `private-user.123` - Auth required
- **Presence**: `presence-chat-room` - Auth required, shows online users

## Quick Usage Example

```javascript
// Connect and listen
const pusher = new Pusher(appKey, config);
const channel = pusher.subscribe('chat-room');

channel.bind('MessageSent', (data) => {
    displayMessage(data.message, data.user);
});

// Send message (via API)
fetch('/api/chat/send', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ message: 'Hello!' })
});
```

## Troubleshooting

- **Connection failed**: Check host/port in config
- **Auth failed**: Verify token and broadcasting routes
- **No messages**: Check event names match exactly

Enable debug mode:
```javascript
Pusher.logToConsole = true;
```