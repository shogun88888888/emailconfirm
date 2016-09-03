<?php
namespace App\Services;

use App\Models\Users\User;
use App\Repositories\ActivationRepository;
use Illuminate\Mail\Mailer;
use Illuminate\Mail\Message;

class ActivationService
{
        /*
    |--------------------------------------------------------------------------
    | Signup Activation Confirmation Email
    |--------------------------------------------------------------------------
    |
    | Trait to confirm registration activation by email
    |
    */

    protected $mailer;

    protected $activationRepo;

    protected $resendAfter = 24;

    public function __construct(Mailer $mailer, ActivationRepository $activationRepo)
    {
        $this->mailer = $mailer;
        $this->activationRepo = $activationRepo;
    }

    public function sendActivationMail($user)
    {

        if ($user->activated || !$this->shouldSend($user)) {
            return;
        }

        $token = $this->activationRepo->createActivation($user);

        $link = route('user.activate', $token);
        $message = sprintf('Activate account <a href="%s">%s</a>', $link, $link);

        $this->mailer->raw($message, function (Message $m) use ($user) {
            $m->to($user->email)->subject('Activation mail');
        });

        /*
        Mail::send('emails.register',
            array(
                'first_name' => $data['first_name'],
                'email' => $data['email'],
                'username' => $data['username'],
            ), function($message)
            {
                $message->from(env('MAIL_SENDER'));
                $message->to($data['email'], $data['first_name']);
                $message->subject('Palazzo di Bocce Registration Confirmation');
                $message->body = sprintf('All employees must activae their account<a href="%s">%s</a>', $link, $link);
            }
        );
        */


    }

    public function activateUser($token)
    {
        $activation = $this->activationRepo->getActivationByToken($token);

        if ($activation === null) {
            return null;
        }

        $user = User::find($activation->user_id);

        $user->activated = true;

        $user->save();

        $this->activationRepo->deleteActivation($token);

        return $user;

    }

    private function shouldSend($user)
    {
        $activation = $this->activationRepo->getActivation($user);
        return $activation === null || strtotime($activation->created_at) + 60 * 60 * $this->resendAfter < time();
    }

}