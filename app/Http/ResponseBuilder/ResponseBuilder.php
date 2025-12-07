<?php

namespace App\Http\ResponseBuilder;

use App\Constants\ApiCodes;
use Illuminate\Database\Eloquent\Model;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as Builder;

class ResponseBuilder extends Builder
{
    protected function buildResponse(bool $success, int $api_code,
                                          $message_or_api_code, array $lang_args = null,
                                          $data = null, array $debug_data = null): array
    {
        // tell ResponseBuilder to do all the heavy lifting first
        $tmpResponse = parent::buildResponse($success, $api_code, $message_or_api_code, $lang_args, $data, $debug_data);

        if ($tmpResponse['success']) {
            return (array)(
                ($data instanceof Model ? $tmpResponse['data']->item : $tmpResponse['data'])
                ?? ['message' => $tmpResponse['message']]
            );
        }
        $response = [
            'code' => ApiCodes::convertToReadable($tmpResponse['code']),
            'message' => $tmpResponse['message'] ?? null,
        ];
        if (isset($tmpResponse['data'])) {
            $response['errors'] = $tmpResponse['data'];
        }
        if (isset($tmpResponse['debug'])) {
            $response['debug'] = $tmpResponse['debug'];
        }
        return $response;
    }
}
