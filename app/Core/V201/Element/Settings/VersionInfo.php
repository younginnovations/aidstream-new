<?php

namespace App\Core\V201\Element\Settings;

use App\Core\Elements\BaseElement;
use App;

class VersionInfo extends BaseElement
{

    public function getForm()
    {
        return "App\Core\V201\Forms\Settings\OrganizationForm";
    }

    public function getVersionInfoFrom()
    {
        return "App\Core\V201\Forms\Settings\VersionInfoForm";
    }

    public function getRepository()
    {
        return App::make('App\Core\V201\Repositories\Organization\OrganizationRepository');
    }

}