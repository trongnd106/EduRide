<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Collection;

class NotificationService
{
    protected $fcmService;

    public function __construct(FCMService $fcmService)
    {
        $this->fcmService = $fcmService;
    }

    /**
     * Send notification to user
     */
    public function sendNotification(
        User $user,
        string $title,
        string $body,
        string $type,
        array $data = [],
        ?string $imageUrl = null
    ): ?Notification {
        // Save notification to database
        $notification = Notification::create([
            'user_id' => $user->id,
            'title' => $title,
            'body' => $body,
            'type' => $type,
            'data' => $data,
            'image_url' => $imageUrl,
        ]);

        // Send FCM push notification
        if ($user->fcm_token) {
            $fcmData = array_merge($data, [
                'notification_id' => (string) $notification->id,
                'type' => $type,
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            ]);

            $this->fcmService->sendToDevice(
                $user->fcm_token,
                $title,
                $body,
                $fcmData,
                $imageUrl
            );
        }

        return $notification;
    }

    /**
     * Send notification to multiple users
     */
    public function sendNotificationToUsers(
        Collection $users,
        string $title,
        string $body,
        string $type,
        array $data = [],
        ?string $imageUrl = null
    ): void {
        foreach ($users as $user) {
            $this->sendNotification($user, $title, $body, $type, $data, $imageUrl);
        }
    }

    /**
     * Send trip start notification
     */
    public function sendTripStartNotification($trip): void
    {
        // Notify driver
        if ($trip->driver && $trip->driver->user) {
            $this->sendNotification(
                $trip->driver->user,
                '🚌 Chuyến xe bắt đầu',
                "Chuyến {$trip->name} đã bắt đầu lúc " . now()->format('H:i'),
                Notification::TYPE_TRIP_START,
                [
                    'trip_id' => $trip->id,
                    'trip_name' => $trip->name,
                    'start_time' => $trip->start_time,
                ]
            );
        }

        // Notify parents of students on this trip
        $students = $trip->students()->with('parent.user')->get();

        foreach ($students as $student) {
            if ($student->parent && $student->parent->user) {
                $this->sendNotification(
                    $student->parent->user,
                    '🚌 Xe đón con bắt đầu',
                    "Xe đón {$student->full_name} đã bắt đầu chuyến đi",
                    Notification::TYPE_TRIP_START,
                    [
                        'trip_id' => $trip->id,
                        'student_id' => $student->id,
                        'student_name' => $student->full_name,
                    ]
                );
            }
        }
    }

    /**
     * Send trip end notification
     */
    public function sendTripEndNotification($trip): void
    {
        if ($trip->driver && $trip->driver->user) {
            $this->sendNotification(
                $trip->driver->user,
                '✅ Chuyến xe kết thúc',
                "Chuyến {$trip->name} đã hoàn thành",
                Notification::TYPE_TRIP_END,
                [
                    'trip_id' => $trip->id,
                    'trip_name' => $trip->name,
                    'end_time' => now()->format('H:i'),
                ]
            );
        }
    }

    /**
     * Send trip reminder notification
     */
    public function sendTripReminderNotification($trip, int $minutesBefore = 15): void
    {
        if ($trip->driver && $trip->driver->user) {
            $this->sendNotification(
                $trip->driver->user,
                '⏰ Nhắc nhở chuyến xe',
                "Chuyến {$trip->name} sẽ bắt đầu sau {$minutesBefore} phút",
                Notification::TYPE_TRIP_REMINDER_START,
                [
                    'trip_id' => $trip->id,
                    'trip_name' => $trip->name,
                    'minutes_before' => $minutesBefore,
                ]
            );
        }
    }

    /**
     * Send attendance notification to parent
     */
    public function sendAttendanceNotification($attendance): void
    {
        $student = $attendance->student;

        if ($student->parent && $student->parent->user) {
            $status = $attendance->flag === 1 ? 'đã lên xe' : 'đã xuống xe';

            $this->sendNotification(
                $student->parent->user,
                '📋 Điểm danh học sinh',
                "{$student->full_name} {$status} lúc " . $attendance->time_stamp->format('H:i'),
                Notification::TYPE_ATTENDANCE,
                [
                    'attendance_id' => $attendance->id,
                    'student_id' => $student->id,
                    'student_name' => $student->full_name,
                    'flag' => $attendance->flag,
                    'time' => $attendance->time_stamp->toIso8601String(),
                    'point_id' => $attendance->point_id,
                ]
            );
        }
    }
}
