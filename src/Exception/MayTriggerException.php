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
     * @return void
     * @throws Exception
     */
    public function randomExceptionTrigger(Request $request) 
    {
        $mayTriggerError = "true" === $request->query->get('mayTriggerError') ? true : false;
        if ($mayTriggerError) {

            $randomNumber = mt_rand(0, 1);
            if (1 === $randomNumber) {
                throw new Exception('Oops! An Error Occured');
            }
        }
    }
}