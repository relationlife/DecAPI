<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Helpers\Helper;
use Exception;

class MathController extends Controller
{
    /**
     * Base URL for math.js API.
     *
     * @var string
     */
    private $mathBaseUrl = 'https://math.decapi.net/?expr=';

    /**
     * Evaluates a math expression.
     *
     * @param  Request $request
     * @return Response
     */
    public function evaluate(Request $request)
    {
        $exp = $request->input('exp', null);
        $round = intval($request->input('round', 0));

        if (empty($exp)) {
            return Helper::text('A math expression (exp) has to be specified.');
        }

        $decimalSeparator = $request->input('separator', '.');

        // Spaces are unnecessary in the expression, and will actually error the evaluation
        // if spaces are used as a 'thousand separator'
        $exp = str_replace(' ', '', $exp);

        if ($decimalSeparator === '.') {
            // API does not like comma used as a thousand separator
            $exp = str_replace(',', '', $exp);
        } else {
            $exp = str_replace('.', '', $exp);
            $exp = str_replace($decimalSeparator, '.', $exp);
        }

        try {
            $result = (string) Helper::get($this->mathBaseUrl . urlencode($exp), [], false);

            if ($request->exists('round') === true) {
                // Before we can use `round()` we need to convert it to a float.
                $result = floatval($result);
                $result = round($result, $round);
            }

            return Helper::text($result);
        } catch (RuntimeException $e) {
            return Helper::text('An error occurred evaluating: ' . $exp);
        }
    }
}
