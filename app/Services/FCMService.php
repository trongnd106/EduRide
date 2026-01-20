<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FcmNotification;
use Kreait\Firebase\Messaging\AndroidConfig;
use Exception;
use Illuminate\Support\Facades\Log;

class FCMService
{
    protected $messaging;

    public function __construct()
    {
        Log::info('🔄 [FCM] Starting initialization...');

        try {
            // Step 1: Check config
            $credentialsPath = config('firebase.credentials.file');
            Log::info('📍 [FCM] Config loaded', [
                'path' => $credentialsPath,
                'exists' => file_exists($credentialsPath ?? ''),
            ]);

            if (!$credentialsPath) {
                throw new Exception('Firebase credentials path not configured in config/firebase.php');
            }

            if (!file_exists($credentialsPath)) {
                throw new Exception("Firebase credentials file not found at: {$credentialsPath}");
            }

            // Step 2: Create factory
            Log::info('🏭 [FCM] Creating factory...');
            $factory = (new Factory)->withServiceAccount($credentialsPath);
            Log::info('✅ [FCM] Factory created');

            // Step 3: Create messaging
            Log::info('📨 [FCM] Creating messaging instance...');
            $this->messaging = $factory->createMessaging();

            if ($this->messaging === null) {
                throw new Exception('Messaging instance is null after creation');
            }

            Log::info('✅ [FCM] Messaging created successfully', [
                'class' => get_class($this->messaging)
            ]);

        } catch (Exception $e) {
            Log::error('❌ [FCM] Initialization FAILED', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->messaging = null;

            throw new Exception('FCM Service initialization failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Send notification to a single device
     */
    public function sendToDevice(
        string $fcmToken,
        string $title,
        string $body,
        array $data = [],
        ?string $imageUrl = null
    ): bool {
        try {
            $notification = FcmNotification::create($title, $body);

            if ($imageUrl) {
                $notification = $notification->withImageUrl($imageUrl);
            }

            $message = CloudMessage::withTarget('token', $fcmToken)
                ->withNotification($notification)
                ->withData($data)
                ->withAndroidConfig(
                    AndroidConfig::fromArray([
                        'priority' => 'high',
                        'notification' => [
                            'sound' => 'default',
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                            'channel_id' => 'high_importance_channel',
                        ],
                    ])
                );

            $this->messaging->send($message);

            Log::info("FCM sent successfully to token: {$fcmToken}");
            return true;
        } catch (Exception $e) {
            Log::error("FCM send failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send notification to multiple devices
     */
    public function sendToMultipleDevices(
        array $fcmTokens,
        string $title,
        string $body,
        array $data = [],
        ?string $imageUrl = null
    ): array {
        $results = [];

        foreach ($fcmTokens as $token) {
            $results[$token] = $this->sendToDevice($token, $title, $body, $data, $imageUrl);
        }

        return $results;
    }

    /**
     * Send to topic
     */
    public function sendToTopic(
        string $topic,
        string $title,
        string $body,
        array $data = [],
        ?string $imageUrl = null
    ): bool {
        try {
            $notification = FcmNotification::create($title, $body);

            if ($imageUrl) {
                $notification = $notification->withImageUrl($imageUrl);
            }

            $message = CloudMessage::withTarget('topic', $topic)
                ->withNotification($notification)
                ->withData($data);

            $this->messaging->send($message);

            Log::info("FCM sent to topic: {$topic}");
            return true;
        } catch (Exception $e) {
            Log::error("FCM topic send failed: " . $e->getMessage());
            return false;
        }
    }
}
