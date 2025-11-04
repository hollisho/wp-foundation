<?php

namespace WPFoundation\Http;

use WP_REST_Response;
use WP_Error;

/**
 * HTTP Response 封装类
 * 统一的响应对象
 * 
 * 响应格式: { "code": 0, "data": {...}, "msg": "success" }
 * code: 0 表示成功，非 0 表示失败
 * data: 响应数据
 * msg: 消息说明
 */
class Response
{
    /**
     * 成功响应
     * 
     * @param mixed $data 响应数据
     * @param string $message 成功消息
     * @param int $httpStatus HTTP 状态码
     * @return WP_REST_Response
     */
    public static function success($data = null, string $message = 'success', int $httpStatus = 200): WP_REST_Response
    {
        return new WP_REST_Response([
            'code' => 0,
            'data' => $data,
            'msg' => $message,
        ], $httpStatus);
    }

    /**
     * 错误响应
     * 
     * @param string $message 错误消息
     * @param int $code 错误码（非 0）
     * @param mixed $data 额外数据（可选）
     * @param int $httpStatus HTTP 状态码
     * @return WP_REST_Response
     */
    public static function error(string $message = 'error', int $code = 1, $data = null, int $httpStatus = 400): WP_REST_Response
    {
        return new WP_REST_Response([
            'code' => $code,
            'data' => $data,
            'msg' => $message,
        ], $httpStatus);
    }

    /**
     * 自定义响应
     * 
     * @param int $code 状态码
     * @param mixed $data 数据
     * @param string $message 消息
     * @param int $httpStatus HTTP 状态码
     * @return WP_REST_Response
     */
    public static function make(int $code, $data, string $message, int $httpStatus = 200): WP_REST_Response
    {
        return new WP_REST_Response([
            'code' => $code,
            'data' => $data,
            'msg' => $message,
        ], $httpStatus);
    }

    /**
     * JSON 响应（自定义格式）
     */
    public static function json($data, int $httpStatus = 200): WP_REST_Response
    {
        return new WP_REST_Response($data, $httpStatus);
    }

    /**
     * 未授权响应
     */
    public static function unauthorized(string $message = 'Unauthorized'): WP_REST_Response
    {
        return self::error($message, 401, null, 401);
    }

    /**
     * 禁止访问响应
     */
    public static function forbidden(string $message = 'Forbidden'): WP_REST_Response
    {
        return self::error($message, 403, null, 403);
    }

    /**
     * 未找到响应
     */
    public static function notFound(string $message = 'Not Found'): WP_REST_Response
    {
        return self::error($message, 404, null, 404);
    }

    /**
     * 验证失败响应
     */
    public static function validationError(array $errors, string $message = 'Validation Failed'): WP_REST_Response
    {
        return self::error($message, 422, $errors, 422);
    }

    /**
     * 服务器错误响应
     */
    public static function serverError(string $message = 'Internal Server Error'): WP_REST_Response
    {
        return self::error($message, 500, null, 500);
    }

    /**
     * 参数错误响应
     */
    public static function badRequest(string $message = 'Bad Request'): WP_REST_Response
    {
        return self::error($message, 400, null, 400);
    }

    /**
     * 从 WP_Error 创建响应
     */
    public static function fromWpError(WP_Error $error, int $code = 1, int $httpStatus = 400): WP_REST_Response
    {
        return self::error(
            $error->get_error_message(),
            $code,
            $error->get_error_data(),
            $httpStatus
        );
    }

    /**
     * 分页响应
     * 
     * @param array $items 数据列表
     * @param int $total 总数
     * @param int $page 当前页
     * @param int $perPage 每页数量
     * @param string $message 消息
     * @return WP_REST_Response
     */
    public static function paginate(array $items, int $total, int $page, int $perPage, string $message = 'success'): WP_REST_Response
    {
        return self::success([
            'items' => $items,
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage),
            ],
        ], $message);
    }
}

