<?php

namespace Drupal\xtools\Form;


class FormValuesExtractor
{

    public function extract(\Drupal\Core\Form\FormStateInterface $state)
    {
        $values = [];
        $form = $state->getCompleteForm();

        foreach ($state->cleanValues()->getValues() as $key => $value) {

            $values[$key] = [
                'title' => $form[$key]['#title'] ?? ucfirst($key),
                'value' => $value
            ];
        }

        return $values;
    }
}