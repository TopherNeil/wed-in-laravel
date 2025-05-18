<?php

namespace App\Traits;

trait Response
{
    public function success(array $data = [], $code = 200)
    {
        return response()->json($data, $code);
    }

    public function error(array $data = [], $code = 500)
    {
        return response()->json($data, $code);
    }
}
