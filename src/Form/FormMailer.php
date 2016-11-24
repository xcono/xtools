<?php

namespace Drupal\xtools\Form;

use Drupal\Core\Mail\MailManagerInterface;

class FormMailer
{

    private $mailManager;
    private $formValuesExtractor;

    /**
     * FormMailer constructor.
     * @param $mailManager
     */
    public function __construct(MailManagerInterface $mailManager)
    {
        $this->mailManager = $mailManager;
        $this->formValuesExtractor = new FormValuesExtractor();
    }


    public function getValues(\Drupal\Core\Form\FormStateInterface $state)
    {
        $build = [];

        foreach ($this->formValuesExtractor->extract($state) as $key => $value) {

            $build[$key] = [
                '#prefix' => $value['title'],
                '#markup' => ': ' . $value['value'],
                '#suffix' => PHP_EOL,
                '#allowed_tags' => ['b'],
            ];
        }

        return $build;
    }

    public function send($title, $message, $to, $from = null)
    {
        $params = [
            'title' => $title,
            'message' => $message,
            'from' => $from
        ];


        $result = $this->mailManager->mail('xcore', 'default', $to, \Drupal::currentUser()->getPreferredLangcode(), $params, null, true);

        if ($result['result'] !== true) {

            $message = t('There was a problem sending your email notification to @email.', array('@email' => $to));
            \Drupal::logger('mail-log')->error($message);

            return false;
        }

        $message = t('An email notification has been sent to @email ', array('@email' => $to));
        \Drupal::logger('mail-log')->notice($message);

        return true;
    }
}