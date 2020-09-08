<?php

namespace App\Exception;

use Exception;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method void randomExceptionTrigger(Request $request)
 */
class MayTriggerException
{
    /**
     * @param Request $request
     * @return void
     * @throws ApiException
     */
    public function randomExceptionTrigger(Request $request)
    {
        $mayTriggerError = "true" === $request->query->get('mayTriggerError');
        if ($mayTriggerError) {
            $randomNumber = mt_rand(0, 1);
            if (1 === $randomNumber) {
                $randomNumberAgain = mt_rand(0, 1);
                if (1 === $randomNumberAgain) {
                    throw new Exception('Oops! An Error Occured');
                }
                throw new ApiException('Oops! An Error Occured');
            }
        }
    }
}
