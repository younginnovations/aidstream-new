<?php namespace App\Core\V201\Forms\Activity;

use Kris\LaravelFormBuilder\Form;

class NarrativeForm extends Form
{
    protected $showFieldErrors = true;

    public function buildForm()
    {
        $json     = file_get_contents(
            app_path("Core/V201/Codelist/" . config('app.locale') . "/Organization/LanguageCodelist.json")
        );
        $response = json_decode($json, true);
        $language = $response['Language'];
        $code_arr = [];
        foreach ($language as $val) {
            $code_arr[$val['code']] = $val['code'] . ' - ' . $val['name'];
        }
        $this
            ->add('narrative', 'text', ['label' => 'Text', 'rules' => 'required'])
            ->add(
                'language',
                'select',
                [
                    'choices' => $code_arr,
                    'label'   => 'Language'
                ]
            );
    }
}
