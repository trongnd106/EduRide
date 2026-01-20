<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;


class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/notifications",
     *     summary="Get all notifications for authenticated user",
     *     description="Retrieves a paginated list of notifications for the authenticated user with optional filtering",
     *     operationId="getNotifications",
     *     tags={"Notifications"},
     *     security={{"passport":{}}},
     *     @OA\Parameter(
     *         name="is_read",
     *         in="query",
     *         description="Filter by read status (0 = unread, 1 = read)",
     *         required=false,
     *         @OA\Schema(type="boolean", example=false)
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Filter by notification type (trip_start, trip_end, attendance, emergency, announcement)",
     *         required=false,
     *         @OA\Schema(type="string", example="attendance")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", example=20, default=20)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notifications retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="current_page", type="integer", example=1),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="user_id", type="integer", example=123),
     *                     @OA\Property(property="type", type="string", example="attendance"),
     *                     @OA\Property(property="title", type="string", example="🚌 Điểm danh học sinh"),
     *                     @OA\Property(property="body", type="string", example="Nguyễn Văn A đã lên xe tại Số 1 Đại Cồ Việt lúc 07:30"),
     *                     @OA\Property(
     *                         property="data",
     *                         type="object",
     *                         @OA\Property(property="student_id", type="integer", example=10),
     *                         @OA\Property(property="trip_id", type="integer", example=5)
     *                     ),
     *                     @OA\Property(property="is_read", type="boolean", example=false),
     *                     @OA\Property(property="read_at", type="string", format="date-time", nullable=true, example=null),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2026-01-19T07:30:00.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2026-01-19T07:30:00.000000Z")
     *                 )
     *             ),
     *             @OA\Property(property="per_page", type="integer", example=20),
     *             @OA\Property(property="total", type="integer", example=50)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - User not authenticated"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Notification::where('user_id', $user->id);

        // Filter by read/unread
        if ($request->has('is_read')) {
            $query->where('is_read', $request->boolean('is_read'));
        }

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $notifications = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return ResponseBuilder::success($notifications);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/notifications/unread-count",
     *     summary="Get unread notifications count",
     *     description="Returns the total number of unread notifications for the authenticated user",
     *     operationId="getUnreadCount",
     *     tags={"Notifications"},
     *     security={{"passport":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Unread count retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="count", type="integer", example=5, description="Number of unread notifications")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - User not authenticated"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function unreadCount()
    {
        $user = Auth::user();
        $count = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        return ResponseBuilder::success(['count' => $count]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/notifications/{id}",
     *     summary="Get notification by ID",
     *     description="Retrieves a specific notification and automatically marks it as read if it hasn't been read yet",
     *     operationId="getNotificationById",
     *     tags={"Notifications"},
     *     security={{"passport":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the notification",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notification retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="user_id", type="integer", example=123),
     *             @OA\Property(property="type", type="string", example="attendance"),
     *             @OA\Property(property="title", type="string", example="🚌 Điểm danh học sinh"),
     *             @OA\Property(property="body", type="string", example="Nguyễn Văn A đã lên xe"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="student_id", type="integer", example=10),
     *                 @OA\Property(property="trip_id", type="integer", example=5)
     *             ),
     *             @OA\Property(property="is_read", type="boolean", example=true),
     *             @OA\Property(property="read_at", type="string", format="date-time", example="2026-01-19T08:00:00.000000Z"),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2026-01-19T07:30:00.000000Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2026-01-19T08:00:00.000000Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Notification not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Notification not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - User not authenticated"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function show($id)
    {
        $user = Auth::user();
        $notification = Notification::where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }

        // Auto mark as read when viewing
        if (!$notification->is_read) {
            $notification->markAsRead();
        }

        return ResponseBuilder::success($notification);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/notifications/{id}/mark-as-read",
     *     summary="Mark notification as read",
     *     description="Marks a specific notification as read",
     *     operationId="markNotificationAsRead",
     *     tags={"Notifications"},
     *     security={{"passport":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the notification",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notification marked as read successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="is_read", type="boolean", example=true),
     *             @OA\Property(property="read_at", type="string", format="date-time", example="2026-01-19T08:00:00.000000Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Notification not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Notification not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function markAsRead($id)
    {
        $user = Auth::user();
        $notification = Notification::where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
            'data' => $notification
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/notifications/mark-all-as-read",
     *     summary="Mark all notifications as read",
     *     description="Marks all unread notifications of the authenticated user as read",
     *     operationId="markAllAsRead",
     *     tags={"Notifications"},
     *     security={{"passport":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="All notifications marked as read successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="All notifications marked as read")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function markAllAsRead()
    {
        $user = Auth::user();

        Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read'
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/notifications/{id}",
     *     summary="Delete notification",
     *     description="Deletes a specific notification by ID",
     *     operationId="deleteNotification",
     *     tags={"Notifications"},
     *     security={{"passport":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the notification to delete",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notification deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Notification deleted")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Notification not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Notification not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $notification = Notification::where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted'
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/notifications/read/all",
     *     summary="Delete all read notifications",
     *     description="Deletes all read notifications of the authenticated user",
     *     operationId="deleteAllReadNotifications",
     *     tags={"Notifications"},
     *     security={{"passport":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Read notifications deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="deleted_count", type="integer", example=15, description="Number of deleted notifications"),
     *             @OA\Property(property="message", type="string", example="Deleted 15 notifications")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function deleteAllRead()
    {
        $user = Auth::user();

        $count = Notification::where('user_id', $user->id)
            ->where('is_read', true)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => "Deleted {$count} notifications",
            'data' => ['deleted_count' => $count]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/notifications/fcm-token",
     *     summary="Update FCM token for push notifications",
     *     description="Updates the Firebase Cloud Messaging (FCM) token for the authenticated user to enable push notifications",
     *     operationId="updateFcmToken",
     *     tags={"Notifications"},
     *     security={{"passport":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"fcm_token"},
     *             @OA\Property(
     *                 property="fcm_token",
     *                 type="string",
     *                 example="fJZvLx8YQEa9rZ3X4jK1mN:APA91bH7K9ZwQ2X5...",
     *                 description="Firebase Cloud Messaging token from mobile device"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="FCM token updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="fcm_token", type="string", example="fJZvLx8YQEa9rZ3X4jK1mN:APA91bH7K9ZwQ2X5..."),
     *             @OA\Property(property="message", type="string", example="FCM token updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="The fcm token field is required.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function updateFcmToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fcm_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $user = Auth::user();
        $user->fcm_token = $request->fcm_token;
        $user->save();
        return response()->json([
            'success' => true,
            'message' => 'FCM token updated successfully',
            'data' => ['fcm_token' => $user->fcm_token]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/notifications/test-send",
     *     summary="Test send notification (Development only)",
     *     description="Sends a test notification to the authenticated user. Only available in development/staging environments.",
     *     operationId="testSendNotification",
     *     tags={"Notifications"},
     *     security={{"passport":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"title", "body"},
     *             @OA\Property(property="title", type="string", example="🧪 Test Notification", description="Notification title"),
     *             @OA\Property(property="body", type="string", example="This is a test notification from API", description="Notification body/message"),
     *             @OA\Property(property="type", type="string", example="test", description="Notification type (optional, default: test)"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 description="Additional custom data (optional)",
     *                 @OA\Property(property="test_key", type="string", example="test_value")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Test notification sent successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="user_id", type="integer", example=123),
     *             @OA\Property(property="type", type="string", example="test"),
     *             @OA\Property(property="title", type="string", example="🧪 Test Notification"),
     *             @OA\Property(property="body", type="string", example="This is a test notification"),
     *             @OA\Property(property="is_read", type="boolean", example=false),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2026-01-19T08:00:00.000000Z"),
     *             @OA\Property(property="message", type="string", example="Test notification sent")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Not allowed in production",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Not allowed in production")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="The title field is required.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function testSendNotification(Request $request)
    {
        if (config('app.env') === 'production') {
            return response()->json([
                'success' => false,
                'message' => 'Not allowed in production'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'type' => 'string',
            'data' => 'array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $user = Auth::user();

        $notification = $this->notificationService->sendNotification(
            $user,
            $request->title,
            $request->body,
            $request->get('type', 'test'),
            $request->get('data', [])
        );

        return response()->json([
            'success' => true,
            'message' => 'Test notification sent',
            'data' => $notification
        ]);
    }
}
