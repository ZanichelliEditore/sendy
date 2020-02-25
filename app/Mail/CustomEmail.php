<?php

namespace App\Mail;

class CustomEmail extends BaseEmail
{
    /**
     * Returns the view to use.
     *
     * @return string
     */
    protected function useView()
    {
        return 'emails.custom-email';
    }
}
