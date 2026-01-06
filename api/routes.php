<?php
// --------------------------------------------------------
// CINE-SYNC MASTER ROUTES
// Maps every API action to a specific File, Class, and Method.
// This is included by index.php
// --------------------------------------------------------

$routes = [
    // --- AUTHENTICATION ---
    "login"          => [ "file" => "AuthController.php", "class" => "AuthController", "method" => "login" ],
    "register"       => [ "file" => "AuthController.php", "class" => "AuthController", "method" => "register" ],
    "profile"        => [ "file" => "AuthController.php", "class" => "AuthController", "method" => "profile" ],
    "update_profile" => [ "file" => "AuthController.php", "class" => "AuthController", "method" => "update_details" ],
    "send_otp"       => [ "file" => "AuthController.php", "class" => "AuthController", "method" => "send_otp" ],
    
    // --- SETTINGS ---
    "change_password"      => [ "file" => "AuthController.php", "class" => "AuthController", "method" => "change_password" ],
    "delete_account"       => [ "file" => "AuthController.php", "class" => "AuthController", "method" => "delete_account" ],
    "update_privacy"       => [ "file" => "AuthController.php", "class" => "AuthController", "method" => "update_privacy" ],
    "toggle_notifications" => [ "file" => "AuthController.php", "class" => "AuthController", "method" => "toggle_notifications" ],
    "toggle_2fa"           => [ "file" => "AuthController.php", "class" => "AuthController", "method" => "toggle_2fa" ],
    "update_language"      => [ "file" => "AuthController.php", "class" => "AuthController", "method" => "update_language" ],
    "update_ads_settings"  => [ "file" => "AuthController.php", "class" => "AuthController", "method" => "update_ads_settings" ],
    "get_login_activity"   => [ "file" => "AuthController.php", "class" => "AuthController", "method" => "get_login_activity" ],

    // --- USER MANAGEMENT ---
    "get_followers"  => [ "file" => "UserController.php", "class" => "UserController", "method" => "getFollowers" ],
    "get_following"  => [ "file" => "UserController.php", "class" => "UserController", "method" => "getFollowing" ],
    "search_users"   => [ "file" => "UserController.php", "class" => "UserController", "method" => "searchUsers" ],
    "user_profile"   => [ "file" => "UserController.php", "class" => "UserController", "method" => "get_user_profile" ],

    // --- CONNECTIONS ---
    "send_request"            => [ "file" => "ConnectionController.php", "class" => "ConnectionController", "method" => "send_request" ],
    "accept_request"          => [ "file" => "ConnectionController.php", "class" => "ConnectionController", "method" => "accept_request" ],
    "get_requests"            => [ "file" => "ConnectionController.php", "class" => "ConnectionController", "method" => "get_requests" ],
    "check_connection_status" => [ "file" => "ConnectionController.php", "class" => "ConnectionController", "method" => "check_status" ],
    "respond_request"         => [ "file" => "ConnectionController.php", "class" => "ConnectionController", "method" => "respond_request" ],
    // Aliases for compatibility
    "send_connection"         => [ "file" => "ConnectionController.php", "class" => "ConnectionController", "method" => "send_request" ], 
    "update_connection"       => [ "file" => "ConnectionController.php", "class" => "ConnectionController", "method" => "respond_request" ],
    "delete_connection"       => [ "file" => "ConnectionController.php", "class" => "ConnectionController", "method" => "delete_connection" ],

    // --- POSTS ---
    "create_post"        => [ "file" => "PostController.php", "class" => "PostController", "method" => "create" ],
    "get_posts"          => [ "file" => "PostController.php", "class" => "PostController", "method" => "getPosts" ],
    "get_posts_for_user" => [ "file" => "PostController.php", "class" => "PostController", "method" => "getPostsForUser" ],

    // --- LIKES ---
    // Fixed: Pointing to LikeController, not PostController
    "like_post"   => [ "file" => "LikeController.php", "class" => "LikeController", "method" => "toggle" ],
    "toggle_like" => [ "file" => "LikeController.php", "class" => "LikeController", "method" => "toggle" ],

    // --- COMMENTS ---
    "get_comments"   => [ "file" => "CommentController.php", "class" => "CommentController", "method" => "getComments" ],
    "create_comment" => [ "file" => "CommentController.php", "class" => "CommentController", "method" => "create" ],
    // Fixed: Alias for Android 'add_comment' calls
    "add_comment"    => [ "file" => "CommentController.php", "class" => "CommentController", "method" => "create" ],

    // --- MESSAGING ---
    "get_conversations" => [ "file" => "MessageController.php", "class" => "MessageController", "method" => "get" ],
    "get_messages"      => [ "file" => "MessageController.php", "class" => "MessageController", "method" => "get" ],
    "get_conversation"  => [ "file" => "MessageController.php", "class" => "MessageController", "method" => "get" ],
    "send_message"      => [ "file" => "MessageController.php", "class" => "MessageController", "method" => "send" ],
    "add_reaction"      => [ "file" => "MessageController.php", "class" => "MessageController", "method" => "add_reaction" ],
    "toggle_favorite"   => [ "file" => "MessageController.php", "class" => "MessageController", "method" => "toggle_favorite" ],

    // --- NOTIFICATIONS ---
    "get_notifications" => [ "file" => "NotificationController.php", "class" => "NotificationController", "method" => "get" ],
    "get_unread_count"  => [ "file" => "NotificationController.php", "class" => "NotificationController", "method" => "getUnreadCount" ],

    // --- UTILITIES (Blocking/Reporting/Password) ---
    "block_user"            => [ "file" => "BlockController.php", "class" => "BlockController", "method" => "block" ],
    "unblock_user"          => [ "file" => "BlockController.php", "class" => "BlockController", "method" => "unblock" ],
    "get_blocked_users"     => [ "file" => "BlockController.php", "class" => "BlockController", "method" => "get_blocked_users" ],
    "submit_report"         => [ "file" => "ReportController.php", "class" => "ReportController", "method" => "submit_report" ],
    "forgot_password_init"  => [ "file" => "ForgetPassController.php", "class" => "ForgetPassController", "method" => "initiateReset" ],
    "forgot_password_reset" => [ "file" => "ForgetPassController.php", "class" => "ForgetPassController", "method" => "resetPassword" ],
    
    // --- HASHTAGS (Legacy/Future) ---
    "create_hashtag" => [ "file" => "HashtagController.php", "class" => "HashtagController", "method" => "create" ],

    // --- SYSTEM ---
    "test" => [ "file" => "TestController.php", "class" => "TestController", "method" => "ping" ]
];
?>