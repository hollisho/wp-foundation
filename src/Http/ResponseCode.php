<?php

namespace WPFoundation\Http;

/**
 * 响应码常量
 * 
 * 0: 成功
 * 1-999: 通用错误
 * 1000-1999: 用户相关错误
 * 2000-2999: 文章相关错误
 * 3000-3999: 权限相关错误
 * 4000-4999: 数据验证错误
 * 5000-5999: 系统错误
 */
class ResponseCode
{
    // 成功
    const SUCCESS = 0;

    // 通用错误 (1-999)
    const ERROR = 1;
    const UNKNOWN_ERROR = 2;
    const INVALID_PARAMS = 3;
    const OPERATION_FAILED = 4;

    // HTTP 状态相关 (400-599)
    const BAD_REQUEST = 400;
    const UNAUTHORIZED = 401;
    const FORBIDDEN = 403;
    const NOT_FOUND = 404;
    const VALIDATION_ERROR = 422;
    const SERVER_ERROR = 500;

    // 用户相关 (1000-1999)
    const USER_NOT_FOUND = 1001;
    const USER_NOT_AUTHENTICATED = 1002;
    const USER_UPDATE_FAILED = 1003;
    const USER_CREATE_FAILED = 1004;
    const USER_DELETE_FAILED = 1005;

    // 文章相关 (2000-2999)
    const POST_NOT_FOUND = 2001;
    const POST_CREATE_FAILED = 2002;
    const POST_UPDATE_FAILED = 2003;
    const POST_DELETE_FAILED = 2004;

    // 权限相关 (3000-3999)
    const PERMISSION_DENIED = 3001;
    const INSUFFICIENT_PERMISSIONS = 3002;
    const TOKEN_INVALID = 3003;
    const TOKEN_EXPIRED = 3004;

    // 数据验证相关 (4000-4999)
    const VALIDATION_FAILED = 4001;
    const REQUIRED_FIELD_MISSING = 4002;
    const INVALID_FORMAT = 4003;
    const DUPLICATE_ENTRY = 4004;

    // 系统错误 (5000-5999)
    const DATABASE_ERROR = 5001;
    const FILE_UPLOAD_ERROR = 5002;
    const EXTERNAL_API_ERROR = 5003;
    const CACHE_ERROR = 5004;

    /**
     * 获取错误码对应的默认消息
     */
    public static function getMessage(int $code): string
    {
        $messages = [
            self::SUCCESS => 'success',
            self::ERROR => 'error',
            self::UNKNOWN_ERROR => 'unknown error',
            self::INVALID_PARAMS => 'invalid params',
            self::OPERATION_FAILED => 'operation failed',

            self::BAD_REQUEST => 'bad request',
            self::UNAUTHORIZED => 'unauthorized',
            self::FORBIDDEN => 'forbidden',
            self::NOT_FOUND => 'not found',
            self::VALIDATION_ERROR => 'validation error',
            self::SERVER_ERROR => 'server error',

            self::USER_NOT_FOUND => 'user not found',
            self::USER_NOT_AUTHENTICATED => 'user not authenticated',
            self::USER_UPDATE_FAILED => 'user update failed',
            self::USER_CREATE_FAILED => 'user create failed',
            self::USER_DELETE_FAILED => 'user delete failed',

            self::POST_NOT_FOUND => 'post not found',
            self::POST_CREATE_FAILED => 'post create failed',
            self::POST_UPDATE_FAILED => 'post update failed',
            self::POST_DELETE_FAILED => 'post delete failed',

            self::PERMISSION_DENIED => 'permission denied',
            self::INSUFFICIENT_PERMISSIONS => 'insufficient permissions',
            self::TOKEN_INVALID => 'token invalid',
            self::TOKEN_EXPIRED => 'token expired',

            self::VALIDATION_FAILED => 'validation failed',
            self::REQUIRED_FIELD_MISSING => 'required field missing',
            self::INVALID_FORMAT => 'invalid format',
            self::DUPLICATE_ENTRY => 'duplicate entry',

            self::DATABASE_ERROR => 'database error',
            self::FILE_UPLOAD_ERROR => 'file upload error',
            self::EXTERNAL_API_ERROR => 'external api error',
            self::CACHE_ERROR => 'cache error',
        ];

        return $messages[$code] ?? 'unknown error';
    }
}
